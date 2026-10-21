<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('blog_posts');

        if (Schema::hasColumn('blog_posts', 'author_id')) {
            $query->leftJoin('users', 'blog_posts.author_id', '=', 'users.id')
                  ->select('blog_posts.*', 'users.name as author_name');
        } else {
            $query->select('blog_posts.*', DB::raw("'Editorial Desk' as author_name"));
        }

        $query->where('blog_posts.status', 'published');

        if ($request->filled('category')) {
            $query->where('blog_posts.category', $request->category);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('blog_posts.title', 'like', "%{$q}%")
                    ->orWhere('blog_posts.content', 'like', "%{$q}%");
            });
        }

        $posts = $query->latest('blog_posts.id')->paginate(9)->withQueryString();
        $categories = DB::table('blog_posts')
            ->where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('frontend.blogs.index', compact('posts', 'categories'));
    }

    public function show(Request $request, $slug)
    {
        $query = DB::table('blog_posts');

        if (Schema::hasColumn('blog_posts', 'author_id')) {
            $query->leftJoin('users', 'blog_posts.author_id', '=', 'users.id')
                  ->select('blog_posts.*', 'users.name as author_name');
        } else {
            $query->select('blog_posts.*', DB::raw("'Editorial Desk' as author_name"));
        }

        $post = $query->where('blog_posts.slug', $slug)
                      ->where('blog_posts.status', 'published')
                      ->first();

        abort_if(!$post, 404);

        if (Schema::hasColumn('blog_posts', 'views_count')) {
            DB::table('blog_posts')->where('id', $post->id)->increment('views_count');
        }

        $relatedPosts = DB::table('blog_posts')
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->latest('id')
            ->take(4)
            ->get();

        $comments = collect();
        $replies = [];

        if (Schema::hasTable('blog_comments')) {
            $currentUserId = auth()->id() ?? 0;
            
            // Query comments
            $commentsQuery = DB::table('blog_comments')
                ->leftJoin('users', 'blog_comments.user_id', '=', 'users.id')
                ->select('blog_comments.*', 'users.name as user_name', 'users.email as user_email')
                ->where('blog_comments.blog_post_id', $post->id)
                ->whereNull('blog_comments.parent_id')
                ->where(function ($w) use ($currentUserId) {
                    $w->where('blog_comments.status', 1);
                    if ($currentUserId) {
                        $w->orWhere(function($sub) use ($currentUserId) {
                            $sub->where('blog_comments.user_id', $currentUserId)->where('blog_comments.status', 0);
                        });
                    }
                })
                ->latest('blog_comments.id');

            $comments = $commentsQuery->paginate(6);

            // Handle invalid pagination page redirect (fixes empty page with pagination bar)
            if ($comments->isEmpty() && $comments->currentPage() > 1) {
                return redirect()->route('blogs.show', ['slug' => $slug, 'page' => max(1, $comments->lastPage())]);
            }

            $rootIds = $comments->pluck('id')->toArray();
            if (!empty($rootIds)) {
                $allReplies = DB::table('blog_comments')
                    ->leftJoin('users', 'blog_comments.user_id', '=', 'users.id')
                    ->select('blog_comments.*', 'users.name as user_name', 'users.email as user_email')
                    ->where('blog_comments.blog_post_id', $post->id)
                    ->whereIn('blog_comments.parent_id', $rootIds)
                    ->where(function ($w) use ($currentUserId) {
                        $w->where('blog_comments.status', 1);
                        if ($currentUserId) {
                            $w->orWhere(function($sub) use ($currentUserId) {
                                $sub->where('blog_comments.user_id', $currentUserId)->where('blog_comments.status', 0);
                            });
                        }
                    })
                    ->oldest('blog_comments.id')
                    ->get();

                foreach ($allReplies as $rep) {
                    $replies[$rep->parent_id][] = $rep;
                }
            }
        }

        return view('frontend.blogs.show', compact('post', 'relatedPosts', 'comments', 'replies'));
    }

    public function postComment(Request $request, $slug)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Please sign in to comment.'], 401);
        }

        $post = DB::table('blog_posts')->where('slug', $slug)->where('status', 'published')->first();
        if (!$post || empty($post->enable_comments)) {
            return response()->json(['status' => 'error', 'message' => 'Comments are disabled for this article.'], 403);
        }

        $request->validate([
            'comment'   => 'required|string|min:2|max:1000',
            'parent_id' => 'nullable|integer|exists:blog_comments,id'
        ]);

        $rawText = trim($request->input('comment'));

        // Anti-Spam: Strip links for normal users
        $user = auth()->user();
        $isAdmin = isset($user->role) && in_array(strtolower($user->role), ['admin', 'superadmin', 'manager']);
        if (!$isAdmin) {
            $rawText = preg_replace('/https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9-]+\.(com|org|net|io|me|xyz|info)[^\s]*/i', '[link removed]', $rawText);
        }

        $cleanComment = e(strip_tags($rawText));
        $autoApprove = DB::table('settings')->where('key', 'blog_comment_auto_approve')->value('value') ?? '1';
        $status = ($isAdmin || $autoApprove == '1') ? 1 : 0;

        $parentId = $request->input('parent_id') ?: null;

        $commentId = DB::table('blog_comments')->insertGetId([
            'blog_post_id' => $post->id,
            'user_id'      => $user->id,
            'parent_id'    => $parentId,
            'comment'      => $cleanComment,
            'status'       => $status,
            'is_edited'    => 0,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // 1. Notify Admin via Email
        try {
            $adminEmail = DB::table('settings')->where('key', 'admin_notification_email')->value('value')
                ?? config('mail.from.address');

            if (!empty($adminEmail)) {
                $postUrl = url('/blog/' . $post->slug);
                $adminPanelUrl = route('admin.content.blogs.comments');
                $mailMsg = "New Comment on: {$post->title}\n"
                         . "User: {$user->name} ({$user->email})\n"
                         . "Comment:\n\"{$cleanComment}\"\n\n"
                         . "Manage/Approve in Admin Panel: {$adminPanelUrl}\n"
                         . "View Article: {$postUrl}";

                Mail::raw($mailMsg, function ($m) use ($adminEmail, $post) {
                    $m->to($adminEmail)->subject("New Blog Comment: " . Str::limit($post->title, 40));
                });
            }
        } catch (\Exception $e) {}

        // 2. Email Notification to original commenter on reply
        if ($parentId) {
            try {
                $parentComment = DB::table('blog_comments')
                    ->leftJoin('users', 'blog_comments.user_id', '=', 'users.id')
                    ->where('blog_comments.id', $parentId)
                    ->select('users.email', 'users.name')
                    ->first();

                if ($parentComment && !empty($parentComment->email) && $parentComment->email !== $user->email) {
                    Mail::raw("Hello {$parentComment->name},\n\n{$user->name} replied to your comment on: '{$post->title}'.\n\nReply:\n\"{$cleanComment}\"\n\nRead here: " . url('/blog/' . $post->slug), function ($m) use ($parentComment, $post) {
                        $m->to($parentComment->email)->subject("Reply received on: " . Str::limit($post->title, 40));
                    });
                }
            } catch (\Exception $e) {}
        }

        return response()->json([
            'status'      => 'success',
            'message'     => $status === 1 ? 'Comment published successfully!' : 'Comment submitted for approval.',
            'status_code' => $status
        ]);
    }

    public function updateComment(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Please login first.'], 401);
        }

        $comment = DB::table('blog_comments')->where('id', $id)->first();
        if (!$comment) {
            return response()->json(['status' => 'error', 'message' => 'Comment not found.'], 404);
        }

        $user = auth()->user();
        $isAdmin = isset($user->role) && in_array(strtolower($user->role), ['admin', 'superadmin']);

        if ($comment->user_id !== $user->id && !$isAdmin) {
            return response()->json(['status' => 'error', 'message' => 'Permission denied.'], 403);
        }

        $request->validate(['comment' => 'required|string|min:2|max:1000']);
        $rawText = trim($request->input('comment'));

        if (!$isAdmin) {
            $rawText = preg_replace('/https?:\/\/[^\s]+|www\.[^\s]+|[a-zA-Z0-9-]+\.(com|org|net|io|me|xyz|info)[^\s]*/i', '[link removed]', $rawText);
        }

        DB::table('blog_comments')->where('id', $id)->update([
            'comment'    => e(strip_tags($rawText)),
            'is_edited'  => 1,
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Comment updated successfully!']);
    }

    public function deleteComment($id)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'error', 'message' => 'Please login first.'], 401);
        }

        $comment = DB::table('blog_comments')->where('id', $id)->first();
        if (!$comment) {
            return response()->json(['status' => 'error', 'message' => 'Comment not found.'], 404);
        }

        $user = auth()->user();
        $isAdmin = isset($user->role) && in_array(strtolower($user->role), ['admin', 'superadmin']);

        if ($comment->user_id !== $user->id && !$isAdmin) {
            return response()->json(['status' => 'error', 'message' => 'Permission denied.'], 403);
        }

        DB::table('blog_comments')->where('parent_id', $id)->delete();
        DB::table('blog_comments')->where('id', $id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Comment deleted successfully.']);
    }
}
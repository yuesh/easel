<?php

namespace Canvas\Http\Controllers\Frontend;

use Auth;
use Canvas\Models\Tag;
use Canvas\Models\Post;
use Canvas\Models\User;
use Canvas\Models\Settings;
use Illuminate\Http\Request;
use Canvas\Jobs\BlogIndexData;
use Canvas\Http\Controllers\Controller;

class BlogController extends Controller
{
    /**
     * Display the blog index page.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tag = $request->get('tag');
        $data = $this->dispatch(new BlogIndexData($tag));
        $layout = $tag ? Tag::layout($tag)->first() : config('blog.tag_layout');
        $css = Settings::customCSS();
        $js = Settings::customJS();

        return view($layout, $data, compact('css', 'js'));
    }

    /**
     * Display a blog post page.
     *
     * @param $slug
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showPost($slug, Request $request)
    {
        $post = Post::with('tags')->whereSlug($slug)->firstOrFail();
        $user = User::where('id', $post->user_id)->firstOrFail();
        $tag = $request->get('tag');
        $title = $post->title;
        $css = Settings::customCSS();
        $js = Settings::customJS();

        if ($tag) {
            $tag = Tag::whereTag($tag)->firstOrFail();
        }

        if ($post->is_draft && ! Auth::check()) {
            return redirect()->route('blog.post.index');
        }

        return view($post->layout, compact('post', 'tag', 'slug', 'title', 'user', 'css', 'js'));
    }
}

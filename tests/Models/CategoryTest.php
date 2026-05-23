<?php

namespace YellowThree\Voyager\Tests;

use Illuminate\Support\Facades\Auth;
use YellowThree\Voyager\Models\Category;
use YellowThree\Voyager\Models\Post;

class CategoryTest extends TestCase
{
    public function testCanCreateACategoryWithLoggedInUser()
    {
        $user = Auth::loginUsingId(1);

        $category = new Category();

        $category->fill([
            'name' => 'Test Title',
            'slug' => 'test-slug',
        ]);

        $category->save();

        $this->assertEquals('test-slug', $category->slug);
        $this->assertEquals('Test Title', $category->name);
    }

    public function testHasPost()
    {
        $post = Post::first();
        $post->category_id = Category::first()->id;
        $post->save();

        $this->assertTrue($post->category !== null);
        $this->assertTrue($post->category->posts !== null);
    }
}

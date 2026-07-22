<?php use App\Models\User;
use Carbon\Carbon;use App\Models\Post;
use App\Models\static;

class ImportEdgeController
{
    public function index(): void
    {
        User::find(1);
        Post::find(1);
        static::find(1);
        $this->DB->table('users');
    }
}

function makeController()
{
    class InlineController
    {
        public function index(): void
        {
            DB::table('users')->get();
        }
    }
}

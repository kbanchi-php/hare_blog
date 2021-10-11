<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    private static int $number = 0;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $users = [];
        foreach (\App\Models\User::all() as $user) {
            $users[] = $user->id;
        }

        $file_name = date('YmdHisu') . '_' . self::$number . '_test.jpg';
        $file = UploadedFile::fake()->image($file_name);
        File::move($file, storage_path('app/public/images/posts/' . $file_name));
        self::$number++;

        // $file = $this->faker->image();
        // $file_name = basename($file);
        // File::move($file, storage_path('app/public/images/posts/' . $file_name));

        return [
            'title' => $this->faker->word(),
            'body' => $this->faker->paragraph(),
            'image' => $file_name,
            'user_id' => $users[array_rand($users)],
        ];
    }
}

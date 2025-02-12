<?php

namespace App\DTO\box;

class ReviewResponse
{

    public int $id;
    public int $rating;
    public string $comment;
    public int $user_Id;
    public string $username;
    public string $post_date;

    public function __construct(int $id, int $rating, string $comment, string $username, int $user_Id, string $post_date)
    {
        $this->id = $id;
        $this->rating = $rating;
        $this->comment = $comment;
        $this->user_Id = $user_Id;
        $this->username = $username;
        $this->post_date = $post_date;
    }

}
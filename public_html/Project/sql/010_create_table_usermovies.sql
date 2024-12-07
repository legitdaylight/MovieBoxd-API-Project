CREATE TABLE UserMovies (
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int NOT NULL,
    `movie_id` int NOT NULL,
    `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`movie_id`) REFERENCES Movies (`id`),
    FOREIGN KEY (`user_id`) REFERENCES Users (`id`),
    unique key (`movie_id`, `user_id`)
)
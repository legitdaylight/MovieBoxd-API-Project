<?php
    if(isset($_GET["title"]) && $_GET["filter"])
    {
        $listData = ["title"=>$_GET["title"], "filter"=>$_GET["filter"]];
        $watchedURL = "api/toggle_watched.php" . "?" . http_build_query($listData);
    }
    else
    {
        $watchedURL = "api/toggle_watched.php?title=&filter=";
    }
?>

<?php if (isset($data)) : ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
                <?php se($data, "title"); ?>
            </h5>
            <h6 class="card-subtitle mb-2 text-body-secondary">
                <?php se($data, "release_date"); ?>
            </h6>
            <?php $hasUrls = isset($data["image_url"]); ?>
            <?php if ($hasUrls): ?>
                <div class="card-footer">
                    <ul class="list-group list-group-flush">
                        <?php if (isset($data["image_url"])): ?>
                            <li class="list-group-item">
                                <img src="<?php se($data['image_url']); ?>" alt="Movie Poster" width="200" height="200" >
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (is_logged_in() && isset($data["is_watched"])): ?>
                <div class="card-footer">
                    <form method="POST" action="<?php echo get_url($watchedURL);?>">
                        <input type="hidden" name="movieId" value="<?php se($data, "id"); ?>" />
                        <input type="hidden" name="toggleWatched" />
                        <input type="hidden" name="route" value="<?php se($_SERVER, "PHP_SELF");?>"/>
                        <button style="background-color: transparent; border: none !important;">
                            <?php render_like(["value" => $data["is_watched"]]); ?>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif;
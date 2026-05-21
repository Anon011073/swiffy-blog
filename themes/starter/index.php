<?php $include_part('header'); ?>

<section class="posts">
    <?php foreach ($posts as $post): ?>
        <article class="post">
            <h2><a href="index.php?post=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
            <p><?php echo $post['excerpt']; ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php $include_part('footer'); ?>

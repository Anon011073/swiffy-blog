<div class="widget search-widget">
    <h3>Search</h3>
    <form action="index.php" method="GET" class="search-form">
        <input type="text" name="s" placeholder="Search posts..." value="<?php echo htmlspecialchars($_GET['s'] ?? ''); ?>">
        <button type="submit" class="btn">Go</button>
    </form>
</div>

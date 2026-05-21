<div class="widget widget-search">
    <form action="index.php" method="GET" class="search-form">
        <div class="search-input-wrapper">
            <input type="text" name="s" placeholder="Search insights..." required value="<?php echo htmlspecialchars($_GET['s'] ?? ''); ?>">
            <button type="submit" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
        </div>
    </form>
</div>

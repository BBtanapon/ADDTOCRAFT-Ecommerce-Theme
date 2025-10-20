<?php
/**
 * Elementor CSS Diagnostic
 * Upload this file to your theme root and visit: yoursite.com/wp-content/themes/hello-elementor-child/check-elementor-css.php
 */

require_once "../../../wp-load.php";

if (!current_user_can("administrator")) {
	die("Access denied");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Elementor CSS Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: white; padding: 15px; border-radius: 5px; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h1>🔍 Elementor CSS Diagnostic</h1>

    <h2>1. Elementor Status</h2>
    <?php if (class_exists("\Elementor\Plugin")): ?>
        <p class="success">✅ Elementor is active</p>
        <p class="info">Version: <?php echo ELEMENTOR_VERSION; ?></p>
    <?php else: ?>
        <p class="error">❌ Elementor is NOT active</p>
    <?php endif; ?>

    <h2>2. CSS Upload Directory</h2>
    <?php
    $upload_dir = wp_upload_dir();
    $elementor_css_dir = $upload_dir["basedir"] . "/elementor/css";
    ?>
    <p>Path: <code><?php echo $elementor_css_dir; ?></code></p>
    <?php if (is_dir($elementor_css_dir)): ?>
        <p class="success">✅ Directory exists</p>
        <?php if (is_writable($elementor_css_dir)): ?>
            <p class="success">✅ Directory is writable</p>
        <?php else: ?>
            <p class="error">❌ Directory is NOT writable</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="error">❌ Directory does NOT exist</p>
    <?php endif; ?>

    <h2>3. Template CSS Files</h2>
    <?php
    $templates = get_posts([
    	"post_type" => "elementor_library",
    	"posts_per_page" => 20,
    ]);

    if ($templates):
    	foreach ($templates as $template):
    		echo "<h3>Template: " .
    			esc_html($template->post_title) .
    			" (ID: " .
    			$template->ID .
    			")</h3>";

    		if (class_exists("\Elementor\Core\Files\CSS\Post")) {
    			$css_file = \Elementor\Core\Files\CSS\Post::create(
    				$template->ID,
    			);
    			$css_path = $css_file->get_file_path();

    			echo "<p>CSS File: <code>" .
    				esc_html($css_path) .
    				"</code></p>";

    			if (file_exists($css_path)) {
    				$file_size = filesize($css_path);
    				echo '<p class="success">✅ CSS file exists (Size: ' .
    					$file_size .
    					" bytes)</p>";

    				if ($file_size < 100) {
    					echo '<p class="error">⚠️ Warning: File is very small, might be empty</p>';
    				}

    				// Show first 500 characters
    				$css_content = file_get_contents($css_path);
    				echo "<details><summary>Show CSS Preview</summary>";
    				echo "<pre>" .
    					esc_html(substr($css_content, 0, 500)) .
    					"...</pre>";
    				echo "</details>";
    			} else {
    				echo '<p class="error">❌ CSS file does NOT exist</p>';
    			}
    		}
    	endforeach;
    else:
    	echo '<p class="info">No Elementor templates found</p>';
    endif;
    ?>

    <h2>4. Recommended Actions</h2>
    <ol>
        <li>Go to <strong>Elementor > Tools > Regenerate CSS & Data</strong></li>
        <li>Click <strong>"Regenerate Files"</strong></li>
        <li>Clear all caches (plugin cache, server cache, browser cache)</li>
        <li>Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)</li>
    </ol>

    <p><a href="<?php echo admin_url(
    	"admin.php?page=elementor-tools",
    ); ?>">Go to Elementor Tools</a></p>
</body>
</html>

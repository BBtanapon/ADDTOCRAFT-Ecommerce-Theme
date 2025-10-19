<?php
/**
 * Elementor Login/Logout Widget
 *
 * @package HelloElementorChild
 */

if (!defined("ABSPATH")) {
	exit(); // Exit if accessed directly.
}

class Elementor_Login_Logout_Widget extends \Elementor\Widget_Base
{
	/**
	 * Get widget name
	 */
	public function get_name()
	{
		return "login_logout_menu";
	}

	/**
	 * Get widget title
	 */
	public function get_title()
	{
		return __("Login/Logout Menu", "hello-elementor-child");
	}

	/**
	 * Get widget icon
	 */
	public function get_icon()
	{
		return "eicon-lock-user";
	}

	/**
	 * Get widget categories
	 */
	public function get_categories()
	{
		return ["custom-widgets"];
	}

	/**
	 * Get widget keywords
	 */
	public function get_keywords()
	{
		return ["login", "logout", "account", "register", "user", "member"];
	}

	/**
	 * Register widget controls
	 */
	protected function register_controls()
	{
		// Content Section
		$this->start_controls_section("content_section", [
			"label" => __("Content", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control("show_username", [
			"label" => __("Show Username Greeting", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SWITCHER,
			"label_on" => __("Show", "hello-elementor-child"),
			"label_off" => __("Hide", "hello-elementor-child"),
			"return_value" => "yes",
			"default" => "no",
		]);

		$this->add_control("separator_text", [
			"label" => __("Separator", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => "|",
			"placeholder" => "|",
		]);

		$this->end_controls_section();

		// Logged Out Section
		$this->start_controls_section("logged_out_section", [
			"label" => __(
				"Logged Out (Not Logged In)",
				"hello-elementor-child",
			),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control("login_text", [
			"label" => __("Login Text", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Login", "hello-elementor-child"),
			"placeholder" => __("Login", "hello-elementor-child"),
		]);

		$this->add_control("login_url", [
			"label" => __("Login URL", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::URL,
			"placeholder" => wp_login_url(),
			"default" => [
				"url" => wp_login_url(),
			],
		]);

		$this->add_control("register_text", [
			"label" => __("Register Text", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Register", "hello-elementor-child"),
			"placeholder" => __("Register", "hello-elementor-child"),
		]);

		$this->add_control("register_url", [
			"label" => __("Register URL", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::URL,
			"placeholder" => wp_registration_url(),
			"default" => [
				"url" => wp_registration_url(),
			],
		]);

		$this->end_controls_section();

		// Logged In Section
		$this->start_controls_section("logged_in_section", [
			"label" => __("Logged In", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_CONTENT,
		]);

		$this->add_control("account_text", [
			"label" => __("My Account Text", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("My Account", "hello-elementor-child"),
			"placeholder" => __("My Account", "hello-elementor-child"),
		]);

		$this->add_control("account_url", [
			"label" => __("My Account URL", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::URL,
			"placeholder" => get_permalink(
				get_option("woocommerce_myaccount_page_id"),
			),
			"default" => [
				"url" => get_permalink(
					get_option("woocommerce_myaccount_page_id"),
				),
			],
		]);

		$this->add_control("logout_text", [
			"label" => __("Logout Text", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::TEXT,
			"default" => __("Logout", "hello-elementor-child"),
			"placeholder" => __("Logout", "hello-elementor-child"),
		]);

		$this->add_control("logout_url", [
			"label" => __("Logout URL", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::URL,
			"placeholder" => wp_logout_url(home_url()),
			"default" => [
				"url" => wp_logout_url(home_url()),
			],
		]);

		$this->end_controls_section();

		// Style Section
		$this->start_controls_section("style_section", [
			"label" => __("Style", "hello-elementor-child"),
			"tab" => \Elementor\Controls_Manager::TAB_STYLE,
		]);

		$this->add_control("text_alignment", [
			"label" => __("Alignment", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::CHOOSE,
			"options" => [
				"left" => [
					"title" => __("Left", "hello-elementor-child"),
					"icon" => "eicon-text-align-left",
				],
				"center" => [
					"title" => __("Center", "hello-elementor-child"),
					"icon" => "eicon-text-align-center",
				],
				"right" => [
					"title" => __("Right", "hello-elementor-child"),
					"icon" => "eicon-text-align-right",
				],
			],
			"default" => "left",
			"selectors" => [
				"{{WRAPPER}} .elementor-login-logout-widget" =>
					"text-align: {{VALUE}};",
			],
		]);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				"name" => "text_typography",
				"selector" => "{{WRAPPER}} .elementor-login-logout-widget",
			],
		);

		$this->add_control("text_color", [
			"label" => __("Text Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"selectors" => [
				"{{WRAPPER}} .elementor-login-logout-widget" =>
					"color: {{VALUE}};",
				"{{WRAPPER}} .elementor-login-logout-widget a" =>
					"color: {{VALUE}};",
			],
		]);

		$this->add_control("link_hover_color", [
			"label" => __("Link Hover Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"selectors" => [
				"{{WRAPPER}} .elementor-login-logout-widget a:hover" =>
					"color: {{VALUE}};",
			],
		]);

		$this->add_control("separator_color", [
			"label" => __("Separator Color", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::COLOR,
			"selectors" => [
				"{{WRAPPER}} .elementor-login-logout-widget .separator" =>
					"color: {{VALUE}};",
			],
		]);

		$this->add_responsive_control("spacing", [
			"label" => __("Spacing Between Items", "hello-elementor-child"),
			"type" => \Elementor\Controls_Manager::SLIDER,
			"size_units" => ["px"],
			"range" => [
				"px" => [
					"min" => 0,
					"max" => 50,
					"step" => 1,
				],
			],
			"default" => [
				"unit" => "px",
				"size" => 8,
			],
			"selectors" => [
				"{{WRAPPER}} .elementor-login-logout-widget .separator" =>
					"margin: 0 {{SIZE}}{{UNIT}};",
			],
		]);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend
	 */
	protected function render()
	{
		$settings = $this->get_settings_for_display();

		$login_url = !empty($settings["login_url"]["url"])
			? $settings["login_url"]["url"]
			: wp_login_url();
		$register_url = !empty($settings["register_url"]["url"])
			? $settings["register_url"]["url"]
			: wp_registration_url();
		$account_url = !empty($settings["account_url"]["url"])
			? $settings["account_url"]["url"]
			: get_permalink(get_option("woocommerce_myaccount_page_id"));
		$logout_url = !empty($settings["logout_url"]["url"])
			? $settings["logout_url"]["url"]
			: wp_logout_url(home_url());

		$separator = !empty($settings["separator_text"])
			? $settings["separator_text"]
			: "|";
		?>
        <div class="elementor-login-logout-widget">
            <?php if (is_user_logged_in()): ?>
                <?php // User is logged in
                if ($settings["show_username"] === "yes") {
                	$current_user = wp_get_current_user();
                	echo '<span class="user-greeting">' .
                		sprintf(
                			__("Hello, %s", "hello-elementor-child"),
                			esc_html($current_user->display_name),
                		) .
                		"</span>";
                	echo ' <span class="separator">' .
                		esc_html($separator) .
                		"</span> ";
                } ?>
                <a href="<?php echo esc_url(
                	$account_url,
                ); ?>" class="account-link">
                    <?php echo esc_html($settings["account_text"]); ?>
                </a>
                <span class="separator"><?php echo esc_html(
                	$separator,
                ); ?></span>
                <a href="<?php echo esc_url(
                	$logout_url,
                ); ?>" class="logout-link">
                    <?php echo esc_html($settings["logout_text"]); ?>
                </a>
            <?php // User is not logged in

            	else: ?>
                <?php
            	// User is not logged in
            	?>
                <a href="<?php echo esc_url($login_url); ?>" class="login-link">
                    <?php echo esc_html($settings["login_text"]); ?>
                </a>
                <span class="separator"><?php echo esc_html(
                	$separator,
                ); ?></span>
                <a href="<?php echo esc_url(
                	$register_url,
                ); ?>" class="register-link">
                    <?php echo esc_html($settings["register_text"]); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
	}

	/**
	 * Render widget output in the editor (optional)
	 */
	protected function content_template()
	{
		?>
        <#
        var loginUrl = settings.login_url.url ? settings.login_url.url : '<?php echo wp_login_url(); ?>';
        var registerUrl = settings.register_url.url ? settings.register_url.url : '<?php echo wp_registration_url(); ?>';
        var accountUrl = settings.account_url.url ? settings.account_url.url : '<?php echo get_permalink(
        	get_option("woocommerce_myaccount_page_id"),
        ); ?>';
        var logoutUrl = settings.logout_url.url ? settings.logout_url.url : '<?php echo wp_logout_url(
        	home_url(),
        ); ?>';
        var separator = settings.separator_text ? settings.separator_text : '|';
        #>
        <div class="elementor-login-logout-widget">
            <?php if (is_user_logged_in()): ?>
                <# if (settings.show_username === 'yes') { #>
                    <span class="user-greeting"><?php echo sprintf(
                    	__("Hello, %s", "hello-elementor-child"),
                    	wp_get_current_user()->display_name,
                    ); ?></span>
                    <span class="separator">{{{ separator }}}</span>
                <# } #>
                <a href="{{{ accountUrl }}}" class="account-link">{{{ settings.account_text }}}</a>
                <span class="separator">{{{ separator }}}</span>
                <a href="{{{ logoutUrl }}}" class="logout-link">{{{ settings.logout_text }}}</a>
            <?php else: ?>
                <a href="{{{ loginUrl }}}" class="login-link">{{{ settings.login_text }}}</a>
                <span class="separator">{{{ separator }}}</span>
                <a href="{{{ registerUrl }}}" class="register-link">{{{ settings.register_text }}}</a>
            <?php endif; ?>
        </div>
        <?php
	}
}

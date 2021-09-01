<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.smartsites.com
 * @since      1.0.0
 *
 * @package    Smartsites_Autocorner_Scrape
 * @subpackage Smartsites_Autocorner_Scrape/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smartsites_Autocorner_Scrape
 * @subpackage Smartsites_Autocorner_Scrape/admin
 * @author     SmartSites <frontend@smartsite.com>
 */



class Smartsites_Autocorner_Scrape_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	private $client, $csv_header = [
		"make",
		"model",
		"year",
		"dealers_id",
		"overview",
		"accessories",
		"disclaimer",
		"regular_price",
		"cash_special",
		"cash_special_st",
		"cash_price",
		"sale_price",
		"description",
		"desc",
		"notes",
		"stocknum",
		"vin",
		"miles",
		"extcolor",
		"intcolor",
		"engine",
		"trans",
		"body",
		"doors",
		"fuel",
		"drive_train",
		"mpg_city",
		"mpg_hiway",
		"mpg_combined",
		"vehicle_class",
		"title_status",
		"type",
		"certified",
		"oneowner",
		"odometer_status",
		"categories",
		"repairs",
		"seo_title",
		"seo_meta_description",
		"seo_meta_keywords",
		"photos",
		"trim",
	], $file, $error_file, $error_exists = false, $all_content = true, $all_scraped_data;

	/**
	 * The menu ID of this plugin, as returned by add_management_page().
	 *
	 * @var string
	 */
	public $menu_id;

	/**
	 * The capability required to use this plugin.
	 * Please don't change this directly.
	 *
	 * @var string
	 */
	public $capability = 'manage_options';


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $client)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->client = $client;

		if (get_option('smartsites_autocorner_scrape_option') == 'ss-scrape-full-content') {
			$this->all_content = true;
		} else {
			$this->all_content = false;
		}

		add_action('ss_cron_scrape_inventory_sync', [$this, 'crawlInventoryBasedOnMake']);
		add_action('admin_menu', array($this, 'add_admin_menu'));
	}

	/**
	 * Adds a the new item to the admin menu.
	 */
	public function register_fields_for_scraping()
	{
		register_setting('smartsites_autocorner_scrape_section', 'smartsites_autocorner_scrape_option');
		add_settings_section(
			'smartsites_autocorner_scrape_section',
			_x('Select Option', 'text', 'gmb-reviews'),
			null,
			'smartsites-autocorner-scrape'
		);
		add_settings_field(
			'smartsites_autocorner_scrape_option',
			_x('Scraping Option', 'text', 'smartsites-autocorner'),
			[$this, 'scrape_option_SelectField'],
			'smartsites-autocorner-scrape',
			'smartsites_autocorner_scrape_section'
		);
	}

	public function scrape_option_SelectField()
	{
?>
		<select name="smartsites_autocorner_scrape_option">
			<option value="ss-scrape-full-content" <?php selected(get_option(' smartsites_autocorner_scrape_option'), "ss-scrape-full-content") ?>>Scrape Full Content</option>
			<option value="ss-scrape-title" <?php selected(get_option('smartsites_autocorner_scrape_option'), "ss-scrape-title") ?>>Scrape Title Only</option>
		</select>
		<?php
	}




	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smartsites_Autocorner_Scrape_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smartsites_Autocorner_Scrape_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/smartsites-autocorner-scrape-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Smartsites_Autocorner_Scrape_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Smartsites_Autocorner_Scrape_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/smartsites-autocorner-scrape-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Adds a the new item to the admin menu.
	 */
	public function add_admin_menu()
	{
		$this->menu_id = add_management_page(
			_x('SmartSites Scrape', 'admin page title', 'smartsites-autocorner-scrape'),
			_x('SmartSites Scrape', 'admin menu entry title', 'smartsites-autocorner-scrape'),
			$this->capability,
			'smartsites-autocorner-scrape',
			[$this, 'frontend_interface']
		);

		// add_action('admin_head-' . $this->menu_id, array($this, 'add_admin_notice_if_resizing_not_supported'));
	}

	/**
	 * The main Smartsites Autocorner Scrape interface, as displayed at Tools → Smartsites Autocorner Scrape.
	 */
	public function frontend_interface()
	{
		global $wp_version;

		echo '<div class="wrap">';
		echo '<h1>' . esc_html_x('Smartsites Autocorner Scrape', 'admin page title', 'smartsites-autocorner-scrape') . '</h1>';

		if (version_compare($wp_version, '4.7', '<')) {
			echo '<p>' . sprintf(
				__('This plugin requires WordPress 4.7 or newer. You are on version %1$s. Please <a href="%2$s">upgrade</a>.', 'smartsites-autocorner-scrape'),
				esc_html($wp_version),
				esc_url(admin_url('update-core.php'))
			) . '</p>';
		} else {
			echo '<form action="options.php" method="post">';
			settings_fields('smartsites_autocorner_scrape_section');
			do_settings_sections('smartsites-autocorner-scrape');
			submit_button();

			echo '<strong>Please Note:</strong> After updating the option you need to wait until the cron is triggered or manually run the cron event.';
			echo '</form><br>';

			$media_dir = wp_upload_dir()['basedir'];
			if (file_exists($media_dir . '/ss-scrapped-inventory/inventory.csv') || file_exists($media_dir . '/ss-scrapped-inventory/error.txt')) {

		?>
				<div id="smartsites-autocorner-scrape-app">
					<?php if (file_exists($media_dir . '/ss-scrapped-inventory/inventory.csv')) { ?>
						<h2>Scraped Data</h2>
						<div class="csv-download">
							<p><?= "Data last scrapped at: " . date("F d Y H:i:s.", filemtime($media_dir . '/ss-scrapped-inventory/inventory.csv')) . " "; ?></p>
							<a class="button" href="<?= wp_upload_dir()['url'] . '/ss-scrapped-inventory/inventory.csv'; ?>" download>Download Scrapped CSV</a>
						</div>
					<?php }
					if (file_exists($media_dir . '/ss-scrapped-inventory/error.txt')) {
					?>
						<h2>Scraped Data</h2>
						<div class="csv-download">
							<p>Error encountered while scrapping the inventories data, please downlod the error file for debugging</p>
							<a class="button" href="<?= wp_upload_dir()['url'] . '/ss-scrapped-inventory/error.txt'; ?>" download>Download Erorr Log</a>
						</div>
					<?php } ?>
				</div>


			<?php
			} else { ?>
				<div id="smartsites-autocorner-scrape-app">
					<div class="csv-download">
						<h2>Currently the inventory has not beed scrapped. Wait till cron events is triggered or trigger the cron evnet manually.</h2>
					</div>
				</div>
<?php	}
		} // version_compare()

		echo '</div>';
	}



	/** 
	 * Function that return response object
	 * */
	public function fetchMyAutowareHouseUrl()
	{
		try {
			$response = $this->client->request(
				'GET',
				'https://www.myautowarehouse.com/cat_pages/inventory_1.shtml'
			);
		} catch (Exception $e) {
			if ($this->error_exists == false) {
				$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
			}
			$this->error_exists  = true;
			fwrite($this->error_file, $e);
			return;
		}

		return $response;
	}


	/** 
	 * Function to crawl through individual inventories based on the make filter
	 * */
	public function crawlInventoryBasedOnMake()
	{
		if (!file_exists(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory')) {
			mkdir(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory', 0777, true);
		}

		$this->file = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/inventory-scrapping.csv', 'w');
		$get_file = $this->file;
		$csv_head = $this->csv_header;
		fputcsv($get_file, $csv_head, ',', '"', "\t");

		$crawler = $this->fetchMyAutowareHouseUrl();

		try {
			$crawler->filterXPath("//div[@id='list_item']/a")->each(function ($node) use (&$get_file) {

				$get_make = $node->text();
				$uri = $node->link()->getUri();
				$page_crawl = $this->client->request('GET', $uri);


				/** 
				 * Get the data from details page
				 * */
				$this->crawlIndividualIventory($page_crawl, $get_file, $get_make);


				/** 
				 * If there is pagination loop through the pagination to scrape data
				 * */
				$this->crawlPaginationIfItExists($page_crawl, $get_file, $get_make);
			});
		} catch (Exception $e) {
			if ($this->error_exists == false) {
				$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
			}
			$this->error_exists  = true;
			fwrite($this->error_file, $e);
		}


		if ($this->error_exists == true) {
			fclose($this->error_file);
		} else {
			fclose($get_file);
			$media_dir = wp_upload_dir()['basedir'];
			// unlink($media_dir . "/ss-scrapped-inventory/inventory.csv");
			rename($media_dir . "/ss-scrapped-inventory/inventory-scrapping.csv", $media_dir . "/ss-scrapped-inventory/inventory.csv");

			$this->update_the_inventory_title_from_scraped_data($this->all_scraped_data);
		}
	}


	/** 
	 * Function to update the post title
	 * */
	function update_the_inventory_title_from_scraped_data($get_scraped_obj)
	{
		foreach ($get_scraped_obj as $single_item) {
			if (isset($single_item[16]) && isset($single_item[41])) {
				$posts = get_posts(array(
					'post_type' => 'ss_inventory',
					'meta_query' => array(
						array(
							'key' => 'ss_vin',
							'value' => $single_item[16],
						)
					),
					'post_status' => 'any',
					'posts_per_page' => -1,
				));

				if (isset($posts[0])) {
					$post_update = array(
						'ID'         => $posts[0]->ID,
						'post_title' => $single_item[41]
					);
					wp_update_post($post_update);
				}
			}
		}
	}


	/** 
	 * Function to crawl through individual inventories
	 * */
	public function crawlIndividualIventory($page_crawl, $file, $get_make)
	{
		$page_crawl->filter('.thumb')->each(function ($node) use (&$file, $get_make) {

			$final_list = [];

			$link = $node->selectLink('more info')->link();
			$uri = $link->getUri();
			$sub_crawler = $this->client->request('GET', $uri);

			try {
				$title = $sub_crawler->filterXPath("//h3[@class='ymm'][1]")->text();
				$description = $sub_crawler->evaluate("//div[@id='description_content']//p[@class='font5 veh_description']/span[@class='english']")->text();
			} catch (Exception $e) {
				if ($this->error_exists == false) {
					$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
				}
				$this->error_exists  = true;
				fwrite($this->error_file, $e);
			}

			$accessories_details = $this->getInventoryAccessories($sub_crawler);
			$image_url = $this->getImageUrls($sub_crawler);
			$general_details = $this->getGeneralInformation($sub_crawler);

			$get_year = explode(' ', $title);
			$year = $get_year[0];

			if ($this->all_content == true) {
				foreach ($this->csv_header as $single_key) {
					if ($single_key == 'vin') {
						$final_list[] = array_key_exists('vin', $general_details) ? $general_details['vin'][0] : '';
					} else if ($single_key == 'miles') {
						$final_list[] = array_key_exists('miles', $general_details) ? $general_details['miles'][0] : '';
					} else if ($single_key == 'extcolor') {
						$final_list[] = array_key_exists('exterior color', $general_details) ? $general_details['exterior color'][0] : '';
					} else if ($single_key == 'intcolor') {
						$final_list[] = array_key_exists('interior color', $general_details) ? $general_details['interior color'][0] : '';
					} else if ($single_key == 'engine') {
						$final_list[] = array_key_exists('engine', $general_details) ? $general_details['engine'][0] : '';
					} else if ($single_key == 'trans') {
						$final_list[] = array_key_exists('transmission', $general_details) ? $general_details['transmission'][0] : '';
					} else if ($single_key == 'body') {
						$final_list[] = array_key_exists('body', $general_details) ? $general_details['body'][0] : '';
					} else if ($single_key == 'doors') {
						$final_list[] = array_key_exists('doors', $general_details) ? $general_details['doors'][0] : '';
					} else if ($single_key == 'drive_train') {
						$final_list[] = array_key_exists('drive train', $general_details) ? $general_details['drive train'][0] : '';
					} else if ($single_key == 'mpg_city') {
						$final_list[] = array_key_exists('mpg city', $general_details) ? $general_details['mpg city'][0] : '';
					} else if ($single_key == 'mpg_hiway') {
						$final_list[] = array_key_exists('mpg highway', $general_details) ? $general_details['mpg highway'][0] : '';
					} else if ($single_key == 'regular_price') {
						$final_list[] = array_key_exists('regular price', $general_details) ? preg_replace('/\D/', '', $general_details['regular price'][0])  : '';
					} else if ($single_key == 'cash_special') {
						$final_list[] = array_key_exists('cash special', $general_details) ? preg_replace('/\D/', '', $general_details['cash special'][0])  : '';
					} else if ($single_key == 'type') {
						$final_list[] = array_key_exists('type', $general_details) ? $general_details['type'][0] : '';
					} else if ($single_key == 'title_status') {
						$final_list[] = array_key_exists('title status', $general_details) ? $general_details['title status'][0] : '';
					} else if ($single_key == 'photos') {
						$final_list[] = $image_url;
					} else if ($single_key == 'accessories') {
						$final_list[] = $accessories_details;
					} else if ($single_key == 'trim') {
						$final_list[] = $title;
					} else if ($single_key == 'year') {
						$final_list[] = $year;
					} else if ($single_key == 'make') {
						$final_list[] = $get_make;
					} else if ($single_key == 'description') {
						$final_list[] = $description;
					} else {
						$final_list[] = "";
					}
				}
			} else {
				foreach ($this->csv_header as $single_key) {
					if ($single_key == 'vin') {
						$final_list[] = array_key_exists('vin', $general_details) ? $general_details['vin'][0] : '';
					} else if ($single_key == 'trim') {
						$final_list[] = $title;
					} else {
						$final_list[] = "";
					}
				}
			}
			$this->all_scraped_data[] = $final_list;
			fputcsv($file, $final_list, ',', '"', "\t");
		});
	}


	/** 
	 * Get each inventory's image urls
	 * */
	public function getImageUrls($crawler)
	{
		$image_url = '';
		try {
			$crawler->evaluate("//div[@id='gallery_content']/table[@id='container']//td[@align='left']//a/@rel")->each(function ($get_image_url, $count) use (&$image_url) {
				if ($count == 0) {
					$image_url .= 'https://www.myautowarehouse.com' . $get_image_url->text();
				} else {
					$image_url .= ', ' . 'https://www.myautowarehouse.com' . $get_image_url->text();
				}
			});
		} catch (Exception $e) {
			if ($this->error_exists == false) {
				$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
			}
			$this->error_exists  = true;
			fwrite($this->error_file, $e);
		}

		return  $image_url;
	}


	/** 
	 * Get each inventory's general information
	 * */
	public function getGeneralInformation($crawler)
	{
		$general_details = [];
		try {
			$crawler->filterXPath("//div[@id='overview_content']//table[@class='veh_overview']//tr")->each(function ($details) use (&$general_details) {

				if ($details->filterXPath("//td[@align='left']/span[@class='english']")->count() > 0) {
					$left_column = strtolower($details->filterXPath("//td[@align='left']/span[@class='english']")->text());
					$right_column = $details->filterXPath("//td[@align='right']/span[@class='english']")->text();
					$general_details[$left_column][] = $right_column;
				} else {
					$left_column = strtolower($details->filterXPath("//td[@align='left']")->text());
					$right_column = $details->filterXPath("//td[@align='right']")->text();

					$general_details[$left_column][] = $right_column;
				}
			});
		} catch (Exception $e) {
			if ($this->error_exists == false) {
				$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
			}
			$this->error_exists  = true;
			fwrite($this->error_file, $e);
		}

		return $general_details;
	}


	/** 
	 * Get each inventory's accessories details
	 * */
	public function getInventoryAccessories($crawler)
	{
		$accessories_details = '';
		try {
			$crawler->evaluate("//div[@id='accessories_content']//span[@class='english']//ul[@class='font5 features-ul1']/li[@class='list-item2']")->each(function ($accessories, $count) use (&$accessories_details) {
				if ($count == 0) {
					$accessories_details .= $accessories->text();
				} else {
					$accessories_details .= ', ' . $accessories->text();
				}
			});
		} catch (Exception $e) {
			if ($this->error_exists == false) {
				$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
			}
			$this->error_exists  = true;
			fwrite($this->error_file, $e);
		}
		return $accessories_details;
	}


	/** 
	 * Go through pagination links and scrape the individual inentories details
	 * */
	public function crawlPaginationIfItExists($crawler, $file, $get_make)
	{
		try {
			if ($crawler->evaluate("//div[@class='font5 link_area'][1]//span[@class='page_link prev_next']/a")->count() > 0) {

				$crawler->evaluate("//div[@class='font5 link_area'][1]//span[@class='page_link prev_next']/a")->each(function ($next_page_link) use (&$file, $get_make) {
					if (strtolower($next_page_link->text()) == 'next') {
						$uri = $next_page_link->link()->getUri();
						$page_crawl = $this->client->request('GET', $uri);

						/** 
						 * Get the data from details page
						 * */
						$this->crawlIndividualIventory($page_crawl, $file, $get_make);

						/** 
						 * Recursion, If pagination exists
						 * */
						$this->crawlPaginationIfItExists($page_crawl, $file, $get_make);
					}
				});
			}
		} catch (Exception $e) {
			if ($this->error_exists == false) {
				$this->error_file  = fopen(wp_upload_dir()['basedir'] . '/ss-scrapped-inventory/error.txt', 'w');
			}
			$this->error_exists  = true;
			fwrite($this->error_file, $e);
		}
	}
}

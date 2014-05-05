<?php /**
 * A basic sync controller class
 *
 * @package wp-fundaments
 */

class SktSyncController {
	protected $post_type = 'post';
	protected $public = true;
	protected $description = 'Sync posts with data from another server.';
	protected $format = 'json';
	protected $remote_id_property = 'id';
	protected $frequency = SKT_SYNC_FREQ_DAY;
	private $metakey;

	function __construct($plugin, $post_type) {
		$this->plugin = $plugin;
		$this->post_type = $post_type;

		$this->post_type_object = get_post_type_object($post_type);
		if(!is_object($this->post_type_object)) {
			wp_die("Post type <code>$post_type</code> is unrecognised.");
		}

		$this->metakey = '_' . $this->plugin . '_remote_id';
		add_action('admin_menu', array(&$this, 'register'));
		add_action(
			'skt_cron_job_' . md5(__file__),
			array(&$this, 'hook')
		);

		register_activation_hook(
			ABSPATH . '/wp-content/plugins/' . $plugin . '/bootstrap.php',
			array(&$this, 'schedule')
		);
	}

	public function schedule() {
		$next = current_time('timestamp') + $this->frequency;

		wp_schedule_event(
			$next,
			'skt_cron_' . $this->frequency,
			'skt_cron_job_' . md5(__file__)
		);
	}

	public function hook() {
		$this->sync();
	}

	function register() {
		if($this->public) {
			add_submenu_page(
				'edit.php?post_type=' . $this->post_type,
				_('Sync' . $this->post_type_object->labels->name),
				_('Sync'),
				'administrator',
				get_class($this),
				array(&$this, 'page')
			);
		}
	}

	public function page() { ?>
		<div class="wrap">
			<h2>Sync <?php echo htmlentities($this->post_type_object->labels->name); ?></h2>
			<?php if($_SERVER['REQUEST_METHOD'] != 'POST') { ?>
				<form method="post">
					<?php echo wpautop($this->description); ?>
					<?php wp_nonce_field(basename(__file__), 'skt-fundaments-sync'); ?>
					<button type="submit" class="button-primary">Sync <?php echo htmlentities($this->post_type_object->labels->name); ?></button>
				</form>
			<?php } elseif (empty($_POST) || !wp_verify_nonce($_POST['skt-fundaments-sync'], basename(__file__))) {
				wp_die('Not a chance!');
			} else { ?>
				<ul id="skt-sync-log"></ul>
				<iframe id="skt-sync-frame" src="<?php echo plugins_url('skt-fundaments/services/sync.php', 'skt-fundaments'); ?>?plugin=<?php echo urlencode($this->plugin); ?>&amp;type=<?php echo urlencode($this->post_type); ?>&amp;_ts=<?php echo current_time('timestamp'); ?>" frameborder="0" width="0" height="0"></iframe>

				<script>
					jQuery(window).on('message',
						function(e) {
							if(e.originalEvent.data.skt_sync_log) {
								jQuery('#skt-sync-log').append('<li>' + e.originalEvent.data.skt_sync_log + '</li>');
							}
						}
					);
				</script>
			<?php } ?>
		</div>
	<?php }

	public function sync($status_callback = null) {
		$status_callback && call_user_func($status_callback, 'Getting data from remote server');

		try {
			$data = $this->get_data();
		} catch (Exception $ex) {
			$status_callback && call_user_func($status_callback, 'Error getting data: ' . htmlentities($ex->getMessage()));
			return;
		}

		$type = get_post_type_object($this->post_type);

		foreach($this->get_items($data) as $item) {
			$data = $this->get_item_data($item);

			if(isset($data[$this->remote_id_property])) {
				$remote_id = $data[$this->remote_id_property];
				$existing = get_posts(
					array(
						'post_type' => $this->post_type,
						'meta_query' => array(
							array(
								'key' => $this->metakey,
								'value' => $remote_id
							)
						),
						'posts_per_page' => 1
					)
				);

				if(count($existing) == 1) {
					$status_callback && call_user_func($status_callback,
						'Updating ' . htmlentities($type->labels->singular_name) . ' ' . $existing[0]->ID
					);

					$post_id = $existing[0]->ID;
					$this->update_post($post_id, $data, $status_callback);
					$post = get_post($post_id);
				} else {
					$post_id = $this->insert_post($data, $status_callback);
					$status_callback && call_user_func($status_callback,
						'Importing ' . htmlentities($type->labels->singular_name) . ' ' . $post_id
					);

					add_post_meta($post_id, $this->metakey, $remote_id);
					$post = get_post($post_id);
				}
			}
		}

		$status_callback && call_user_func($status_callback, 'All done!');
	}

	protected function get_data() {
		if(isset($this->url) && $this->url) {
			$request = wp_remote_get($this->url,
				array('timeout' => SKT_SYNC_TIMEOUT)
			);

			if(is_wp_error($request)) {
				wp_die($request);
			}

			$response = $request['body'];
			return $this->parse_data($response);
		} else {
			throw new Exception('Method not implemented.');
		}
	}

	protected function parse_data($data) {
		switch($this->format) {
			case 'json':
				return json_decode($data, true);
			case 'xml':
				return simplexml_load_string($data);
			default:
				throw new Exception('Method not implemented.');
		}
	}

	protected function get_items($data) {
		return $data;
	}

	protected function xml_to_array(SimpleXMLElement $xml) {
		$array = (array)$xml;
		$new_array = array();

		foreach(array_slice($array, 0) as $key => $value) {
			if($key == '@attributes') {
				foreach($value as $k => $v) {
					$new_array[(string)$k] = (string)$v;
				}

				continue;
			}

			if($value instanceof SimpleXMLElement) {
				$new_array[$key] = empty($value) ? null : $this->xml_to_array($value);
			} elseif(is_array($value)) {
				$new_array[$key] = array();
				foreach($value as $v) {
					if($v instanceof SimpleXMLElement) {
						$new_array[$key][] = $this->xml_to_array($v);
					} else {
						$new_array[$key][] = (string)$v;
					}
				}
			}
		}

		return $new_array;
	}

	protected function get_item_data($item) {
		if(is_object($item) && get_class($item) == 'SimpleXMLElement') {
			return $this->xml_to_array($item);
		}

		return array_merge($item, array());
	}

	protected function map_data($item) {
		return $item;
	}

	protected function map_attachments($item) {
		return array();
	}

	protected function map_taxonomies($item) {
		return array();
	}

	protected function insert_post($data, $status_callback = null) {
		$post_id = wp_insert_post(
			array(
				'post_type' => $this->post_type,
				'post_status' => 'publish'
			)
		);

		return $this->update_post($post_id, $data, $status_callback);
	}

	protected function update_post($post_id, $data, $status_callback = null) {
		$mapped = $this->map_data($data);
		$attachments = $this->map_attachments($data);
		$taxonomies = $this->map_taxonomies($data);
		$post = array('ID' => $post_id);

		if(isset($mapped['title'])) {
			$post['post_title'] = $mapped['title'];
			unset($mapped['title']);
		}

		if(isset($mapped['date'])) {
			$post['post_date'] = date('Y-m-d H:i:s', $mapped['date']);
			$post['edit_date'] = true;
			unset($mapped['date']);
		}

		if(count($post) > 1) {
			wp_update_post($post);
		}

		foreach($mapped as $key => $value) {
			skt_update_field($key, $value, $post_id);
		}

		if(count($attachments) > 0) {
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');

			foreach($attachments as $attachment) {
				if($attachment_id = $this->download_attachment($post_id, $attachment['url'], $status_callback)) {
					$this->update_attachment($attachment_id, $attachment, $status_callback);
				}
			}
		}

		foreach($taxonomies as $taxonomy => $terms) {
			$this->update_taxonomy_terms($post_id, $taxonomy,
				is_array($terms) ? $terms : array($terms),
				$status_callback
			);
		}

		return $post_id;
	}

	protected function download_attachment($post_id, $url, $status_callback = null) {
		$existing = get_posts(
			array(
				'post_type' => 'attachment',
				'post_parent' => $post_id,
				'meta_query' => array(
					array(
						'key' => $this->metakey,
						'value' => $url
					)
				),
				'posts_per_page' => 1
			)
		);

		if(count($existing) == 1) {
			$filename = get_attached_file($existing[0]->ID, true);
			if(is_file($filename)) {
				return;
			} else {
				wp_delete_attachment($existing[0]->ID, true);
				$status_callback && call_user_func($status_callback, "Re-downloading $url");
			}
		} else {
			$status_callback && call_user_func($status_callback, "Downloading $url");
		}

		if(is_wp_error($filename)) {
			wp_die($filename);
		}

		$upload_dir = wp_upload_dir();
		$filename = download_url($url);
		$filetype = wp_check_filetype(basename($url));
		$new_filename = $upload_dir['path'] . '/' . uniqid() . '.' . $filetype['ext'];
		rename($filename, $new_filename);

		$attachment_id = wp_insert_attachment(
			array(
				'guid' => $upload_dir['url'] . '/' . basename($new_filename),
				'post_mime_type' => $filetype['type'],
				'post_title' => basename($url),
				'post_content' => '',
				'post_status' => 'inherit'
			),
			$new_filename,
			$post_id
		);

		if(is_wp_error($attachment_id)) {
			wp_die($attachment_id);
		}

		update_post_meta($attachment_id, $this->metakey, $url);
		wp_update_attachment_metadata($attachment_id,
			wp_generate_attachment_metadata($attachment_id, $new_filename)
		);

		set_post_thumbnail($post_id, $attachment_id);
	}

	protected function update_attachment($post_id, $attachment) {

	}

	protected function update_taxonomy_terms($post_id, $taxonomy, $terms, $status_callback = null) {
		$term_ids = array();

		foreach($terms as $term) {
			if($t = get_term_by('name', $term, $taxonomy)) {
				$term_ids[] = $t->ID;
			} else {
				$t = wp_insert_term($term, $taxonomy);
				if(is_wp_error($t)) {
					wp_die($t);
				}

				$term_ids[] = $t['term_id'];
				$status_callback && call_user_func($status_callback, "Creating new $taxonomy term: $term");
			}
		}

		wp_set_post_terms($post_id, $term_ids, $post_id, false);
		$status_callback && call_user_func($status_callback, "Applying $taxonomy terms");
	}

	protected function parse_date($date) {
		return strtotime($date);
	}
}

<?php

namespace MzMboAccess\Session;

use EAMann\Sessionz\Handlers\EncryptionHandler;
use EAMann\Sessionz\Handlers\MemoryHandler;
use EAMann\Sessionz\Manager;
use EAMann\WPSession\SessionHandler;
use EAMann\WPSession\Objects\Option;
use EAMann\WPSession\OptionsHandler;
use EAMann\WPSession\CacheHandler;
use EAMann\WPSession\DatabaseHandler;

/**
 * MzAccessSession wrapper Class
 *
 * @since 1.0.1
 */
class MzAccessSession
{



    // Hold the class instance.
    private static $_instance = null;

    /**
     * Holds our session data
     *
     * @var    array
     * @access private
     * @since  1.0.1
     */
    private $session;

    /**
     * Session index prefix
     *
     * @since  1.0.1
     * @access private
     *
     * @var string
     */
    private $prefix = 'mz_mbo_access_';

    /**
     * Get things started
     *
     * Defines our WP_Session constants, includes the necessary libraries and
     * retrieves the WP Session instance
     *
     * @since 1.0.1
     */
    public function __construct()
    {

        if (! $this->should_start_session()) {
            return;
        }
        if (session_status() !== PHP_SESSION_DISABLED && ( ! defined('WP_CLI') || false === WP_CLI )) {
            add_action('wp_loaded', array( $this, 'wp_session_manager_initialize' ), 1, 0);

            // If we're not in a cron, start the session
            if (! defined('DOING_CRON') || false === DOING_CRON) {
                add_action('wp_loaded', array( $this, 'wp_session_manager_start_session' ), 10, 0);
            }
        }
    }


    /**
     * Initialize the plugin, bootstrap autoloading, and register default hooks
     */
    public function wp_session_manager_initialize()
    {

        if (! isset($_SESSION)) {
            // Queue up the session stack
            $wp_session_handler = Manager::initialize();

            // Fall back to database storage where needed.
            if (defined('WP_SESSION_USE_OPTIONS') && WP_SESSION_USE_OPTIONS) {
                $wp_session_handler->addHandler(new OptionsHandler());
            } else {
                $wp_session_handler->addHandler(new DatabaseHandler());

                /**
                 * The database handler can automatically clean up sessions as it goes. By default,
                 * we'll run the cleanup routine every hour to catch any stale sessions that PHP's
                 * garbage collector happens to miss. This timeout can be filtered to increase or
                 * decrease the frequency of the manual purge.
                 *
                 * @param string $timeout Interval with which to purge stale sessions
                 */
                $timeout = apply_filters('wp_session_gc_interval', 'hourly');

                if (! wp_next_scheduled('wp_session_database_gc')) {
                    wp_schedule_event(time(), $timeout, 'wp_session_database_gc');
                }

                add_action('wp_session_database_gc', array( 'EAMann\WPSession\DatabaseHandler', 'directClean' ));
            }

            // If we have an external object cache, let's use it!
            if (wp_using_ext_object_cache()) {
                $wp_session_handler->addHandler(new CacheHandler());
            }

            if (defined('WP_SESSION_ENC_KEY') && WP_SESSION_ENC_KEY) {
                $wp_session_handler->addHandler(new EncryptionHandler(WP_SESSION_ENC_KEY));
            }

            // Use an in-memory cache for the instance if we can. This will only help in rare cases.
            $wp_session_handler->addHandler(new MemoryHandler());

            $_SESSION['wp_session_manager'] = 'active';
        }

        if (! isset($_SESSION['wp_session_manager']) || $_SESSION['wp_session_manager'] !== 'active') {
            add_action('admin_notices', array( $this, 'wp_session_manager_multiple_sessions_notice' ));
            return;
        }

        // Create the required table.
        DatabaseHandler::createTable();

        register_deactivation_hook(
            __FILE__,
            function () {
                wp_clear_scheduled_hook('wp_session_database_gc');
            }
        );
    }

    /**
     * Determines if we should start sessions
     *
     * @return bool
     * @since  1.0.1
     */
    public function should_start_session()
    {

        $start_session = true;
        if (! empty($_SERVER['REQUEST_URI'])) {
            $blacklist = $this->get_blacklist();
            $uri       = ltrim($_SERVER['REQUEST_URI'], '/');
            $uri       = untrailingslashit($uri);
            if (in_array($uri, $blacklist)) {
                $start_session = false;
            }
            if (false !== strpos($uri, 'feed=')) {
                $start_session = false;
            }
        }

        return apply_filters('mbo_access/sessions/start_session', $start_session);
    }

    /**
     * Retrieve the URI blacklist
     *
     * These are the URIs where we never start sessions
     *
     * @return array
     * @since  1.0.1
     */
    public function get_blacklist()
    {
        $blacklist = apply_filters(
            'mbo_access/sessions/session_start_uri_blacklist',
            array(
                'feed',
                'feed/rss',
                'feed/rss2',
                'feed/rdf',
                'feed/atom',
                'comments/feed',
            )
        );
        // Look to see if WordPress is in a sub folder or this is a network site that uses sub folders
        $folder = str_replace(network_home_url(), '', get_site_url());
        if (! empty($folder)) {
            foreach ($blacklist as $path) {
                $blacklist[] = $folder . '/' . $path;
            }
        }

        return $blacklist;
    }

    /**
     * Main MzAccessSession Instance
     *
     * Ensures only one instance is loaded or can be loaded.
     *
     * @return MzAccessSession - Main instance
     * @since  1.0.1
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * If a session hasn't already been started by some external system, start one!
     */
    public function wp_session_manager_start_session()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Print an admin notice if too many plugins are manipulating sessions.
     *
     * @global array $this->wp_session_messages
     */
    public function wp_session_manager_multiple_sessions_notice()
    {
        echo '<div class="notice notice-error">';
        echo '<p>' . __(
            'Another plugin is attempting to start a session with WordPress. WP Session Manager will not work!',
            'wp-session-manager'
        ) . '</p>';
        echo '</div>';
    }


    /**
     * Cloning is forbidden.
     *
     * @since 1.0.1
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'wsi'), '2.1');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.1
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'wsi'), '2.1');
    }

    /**
     * Retrieve a session variable
     *
     * @access public
     *
     * @param string $key Session key
     *
     * @return mixed Session variable
     * @since  1.5
     */
    public function get($key)
    {
        $key = sanitize_key($key);

        return isset($_SESSION[ $key ]) ? json_decode($_SESSION[ $key ]) : false;
    }

    /**
     * Set a session variable
     *
     * @param string           $key   Session key
     * @param int|string|array $value Session variable
     *
     * @return mixed Session variable
     * @since  1.0.1
     */
    public function set($key, $value)
    {
        $key = sanitize_key($key);

        $_SESSION[ $key ] = wp_json_encode($value);

        return $_SESSION[ $key ];
    }
}

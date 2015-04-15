<?php
/**
 * Advanced AJAX List Table class.
 *
 */
namespace UsabilityDynamics\WPLT {

  if( !defined( 'ABSPATH' ) ) {
    die();
  }

  /**
   * Load WP core classes.
   */
  if( !class_exists( '\WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  }

  if (!class_exists('UsabilityDynamics\SMSRentals\WP_List_Table')) {

    /** ************************ CREATE A PACKAGE CLASS ****************************
     *
     * Create a new list table package that extends the core WP_List_Table class.
     * WP_List_Table contains most of the framework for generating the table, but we
     * need to define and override some methods so that our data can be displayed
     * exactly the way we need it to be.
     *
     * To display this example on a page, you will first need to instantiate the class,
     * then call $yourInstance->prepare_items() to handle any data manipulation, then
     * finally call $yourInstance->display() to render the table to the page.
     *
     * Our theme for this list table is going to be movies.
     */
    class WP_List_Table extends \WP_List_Table {

      /**
       * Additional properties are stored here.
       * It is using __get and __set methods
       */
      private $properties;

      /**
       * @var
       */
      public $_column_headers;

      /**
       * REQUIRED. Set up a constructor that references the parent constructor. We
       * use the parent reference to set some default configs.
       */
      public function __construct( $args ) {

        $screen = get_current_screen();

        //Set parent defaults
        parent::__construct( $args = wp_parse_args( $args, array(
          //singular name of the listed records
          'singular'	=> '',
          //plural name of the listed records
          'plural'	=> '',
          //does this table support ajax?
          'ajax'		=> true,
          // Per Page
          'per_page' => 20,
          // Post Type
          'post_type' => $screen->id,
          'post_status' => 'any',
          // Pagination
          'paged' => 1,
          // Order By
          'orderby' => 'menu_order title',
          'order' => 'asc',
          // Search Filter
          'show_filter' => false,
          // Additional params
          'name' => ( $screen->id . '_' . rand( 101, 999 ) ),
        ) ) );

        foreach( $args as $k => $v ) {
          // May be setup value from Request
          if( isset( $_REQUEST[ $k ] ) ) {
            $args[ $k ] = $_REQUEST[ $k ];
          }
          switch( $k ) {
            case 'per_page':
              /** This filter is documented in wp-admin/includes/post.php */
              $this->per_page = apply_filters( 'edit_posts_per_page', $v, isset( $args[ 'post_type' ] ) ? $args[ 'post_type' ] : false );
              break;
            default:
              $this->{$k} = $v;
              break;
          }
        }

        wp_enqueue_script( 'list-table-ajax', dirname( __DIR__ ) . 'static/scripts/wp-list-table.js', array('jquery') );

      }

      /**
       * Recommended. This method is called when the parent class can't find a method
       * specifically build for a given column. Generally, it's recommended to include
       * one method for each column you want to render, keeping your package class
       * neat and organized. For example, if the class needs to process a column
       * named 'title', it would first see if a method named $this->column_title()
       * exists - if it does, that method will be used. If it doesn't, this one will
       * be used. Generally, you should try to use custom column methods as much as
       * possible.
       *
       * Since we have defined a column_title() method later on, this method doesn't
       * need to concern itself with any column with a name of 'title'. Instead, it
       * needs to handle everything else.
       *
       * For more detailed insight into how columns are handled, take a look at
       * WP_List_Table::single_row_columns()
       *
       * @param array $item A singular item (one full row's worth of data)
       * @param array $column_name The name/slug of the column to be processed
       *
       * @return string Text or HTML to be placed inside the column <td>
       */
      public function column_default( $item, $column_name )
      {
        switch ($column_name) {
          default:
            //Show the whole array for troubleshooting purposes
            if (isset($item->{$column_name}) && is_string( $item->{$column_name} )) {
              return $item->{$column_name};
            } else {
              return 'undefined';
            }
        }
      }

      /**
       * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
       * is given special treatment when columns are processed. It ALWAYS needs to
       * have it's own method.
       *
       * @see WP_List_Table::single_row_columns()
       *
       * @param array $item A singular item (one full row's worth of data)
       *
       * @return string Text to be placed inside the column <td> (movie title only)
       */
      public function column_cb( $post ) {
        return sprintf(
          '<input type="checkbox" name="%1$s[]" value="%2$s" />',
          /*$1%s*/ $this->_args['singular'],  	//Let's simply repurpose the table's singular label ("movie")
          /*$2%s*/ $post->ID			//The value of the checkbox should be the record's id
        );
      }

      /**
       * REQUIRED! This method dictates the table's columns and titles. This should
       * return an array where the key is the column slug (and class) and the value
       * is the column's title text. If you need a checkbox for bulk actions, refer
       * to the $columns array below.
       *
       * The 'cb' column is treated differently than the rest. If including a checkbox
       * column in your table you must create a column_cb() method. If you don't need
       * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
       *
       * @see WP_List_Table::single_row_columns()
       *
       * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
       */
      public function get_columns() {
        return $columns = array(
          'cb'		=> '<input type="checkbox" />', //Render a checkbox instead of text
          'post_title'		=> __( 'Title' ),
        );
      }

      /**
       * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
       * you will need to register it here. This should return an array where the
       * key is the column that needs to be sortable, and the value is db column to
       * sort by. Often, the key and value will be the same, but this is not always
       * the case (as the value is a column name from the database, not the list table).
       *
       * This method merely defines which columns should be sortable and makes them
       * clickable - it does not handle the actual sorting. You still need to detect
       * the ORDERBY and ORDER querystring variables within prepare_items() and sort
       * your data accordingly (usually by modifying your query).
       *
       * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
       */
      public function get_sortable_columns() {
        return $sortable_columns = array(
          'title'	 	=> array( 'post_title', false ),	//true means it's already sorted
        );
      }

      /**
       * Optional. If you need to include bulk actions in your list table, this is
       * the place to define them. Bulk actions are an associative array in the format
       * 'slug'=>'Visible Title'
       *
       * If this method returns an empty value, no bulk action will be rendered. If
       * you specify any bulk actions, the bulk actions box will be rendered with
       * the table automatically on display().
       *
       * Also note that list tables are not automatically wrapped in <form> elements,
       * so you will need to create those manually in order for bulk actions to function.
       *
       * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
       */
      public function get_bulk_actions() {
        return array();
      }

      /**
       * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
       * For this example package, we will handle it in the class to keep things
       * clean and organized.
       *
       * @see $this->prepare_items()
       */
      public function process_bulk_action() {}

      /**
       * REQUIRED! This is where you prepare your data for display. This method will
       * usually be used to query the database, sort and filter the data, and generally
       * get it ready to be displayed. At a minimum, we should set $this->items and
       * $this->set_pagination_args(), although the following properties and methods
       * are frequently interacted with here...
       *
       * @uses $this->_column_headers
       * @uses $this->items
       * @uses $this->get_columns()
       * @uses $this->get_sortable_columns()
       * @uses $this->get_pagenum()
       * @uses $this->set_pagination_args()
       */
      public function prepare_items() {

        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        /**
         * REQUIRED. Finally, we build an array to be used by the class for column
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);

        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();

        /**
         * Prepare Query
         */
        $query = $this->query();

        /**
         * REQUIRED for pagination. Let's check how many items are in our data array.
         * In real-world use, this would be the total number of items in your database,
         * without filtering. We'll need this later, so you should always include it
         * in your own package classes.
         */
        $total_items = $query->found_posts;
        $total_pages = $query->max_num_pages;

        /**
         * REQUIRED. Now we can add our query results to the items property, where
         * it can be used by the rest of the class.
         */
        $this->items = $query->posts;

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
          //WE have to calculate the total number of items
          'total_items'	=> $total_items,
          //WE have to determine how many items to show on a page
          'per_page'	=> $this->per_page,
          //WE have to calculate the total number of pages
          'total_pages'	=> $total_pages,
          // Set ordering values if needed (useful for AJAX)
          'orderby'	=> $this->orderby,
          'order'		=> $this->order,
        ) );

        wp_reset_query();
      }

      /**
       *
       */
      protected function query() {

        return new \WP_Query( array(
          'post_type' => $this->post_type,
          'post_status' => $this->post_status,
          'paged' => $this->paged,
          'posts_per_page' => $this->per_page,
          'orderby' => $this->orderby,
          'order' => $this->order,
        ) );

      }

      /**
       * Display the table
       * Adds a Nonce field and calls parent's display method
       *
       * @since 3.1.0
       * @access public
       */
      function display() {

        echo "<form name=\"{$this->name}\" class=\"wplt_container\" method=\"get\">";

        wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

        echo '<input type="hidden" id="order" name="wplt_class" value="' . get_class( $this ) . '" />';
        echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
        echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';

        if( $this->show_filter ) {
          $this->filter();
        }

        parent::display();

        echo "</form>";
      }

      /**
       * Renders Search Filter
       */
      public function filter() {
        // @TODO
      }

      /**
       * Handle an incoming ajax request (called from admin-ajax.php)
       *
       * @since 3.1.0
       * @access public
       */
      function ajax_response() {

        check_ajax_referer( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );

        $this->prepare_items();

        extract( $this->_args );
        extract( $this->_pagination_args, EXTR_SKIP );

        ob_start();
        if ( ! empty( $_REQUEST['no_placeholder'] ) )
          $this->display_rows();
        else
          $this->display_rows_or_placeholder();
        $rows = ob_get_clean();

        ob_start();
        $this->print_column_headers();
        $headers = ob_get_clean();

        ob_start();
        $this->pagination('top');
        $pagination_top = ob_get_clean();

        ob_start();
        $this->pagination('bottom');
        $pagination_bottom = ob_get_clean();

        $response = array( 'rows' => $rows );
        $response['pagination']['top'] = $pagination_top;
        $response['pagination']['bottom'] = $pagination_bottom;
        $response['column_headers'] = $headers;

        if ( isset( $total_items ) )
          $response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

        if ( isset( $total_pages ) ) {
          $response['total_pages'] = $total_pages;
          $response['total_pages_i18n'] = number_format_i18n( $total_pages );
        }

        return $response;
      }

      /**
       * Store all custom properties in $this->properties
       *
       * @author peshkov@UD
       */
      public function __set($name, $value) {
        $this->properties[$name] = $value;
      }

      /**
       * Get custom properties
       *
       * @author peshkov@UD
       */
      public function __get($name) {
        return isset($this->properties[$name]) ? $this->properties[$name] : NULL;
      }

    }

  }

}
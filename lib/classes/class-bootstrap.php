<?php
/**
 * WP List Table Loader
 *
 * @since 1.0.0
 * @author peshkov@UD
 */
namespace UsabilityDynamics\WPLT {

  if( !class_exists( 'UsabilityDynamics\WPLT\Bootstrap' ) ) {

    final class Bootstrap {

      /**
       * Loader
       *
       * @author peshkov@UD
       */
      public function __construct(){

        // Load AJAX Handler
        new Ajax();

      }

    }

  }

}

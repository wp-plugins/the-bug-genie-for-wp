<?php
/*  Copyright 2012 Raphael Reitzig (wordpress@verrech.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
  // Tell Wordpress what settings to handle and how show them on the options page

  /**
   * PHP has curious restrictions regarding scope of variables. Therefore, the
   * functions used as callbacks in add_settings_field can not be (method) local
   * and access the members of the outer Plugin instance. Therefore, they are
   * defined in this (method local) class.
   *
   * @author Raphael Reitzig
   */
  class Tbg4WpCallbacks {
    private $name = null;
    private $options = null;

    function __construct($name, $options) {
      $this->name = $name;
      $this->options = $options;
    }

    function main_text() {
      echo 'Configure your TBG instance.';
    }

    function tbg_url() {
      echo "<input id='tbg_url' name='{$this->name}[tbg_url]' size='40' type='text' value='{$this->options['tbg_url']}' />\n";
      echo "<br /><small>Enter base directory of <tt>TBG</tt>, e.g. <code>http://example.com/bugs</code>.</small>";
    }
  }
  $callbacks = new Tbg4WpCallbacks($this->name, $this->options);

  add_settings_section('main', 'Main Settings', array(&$callbacks, 'main_text'), $this->name);
  add_settings_field('tbg_url', 'Address of The Bug Genie:', array(&$callbacks, 'tbg_url'), $this->name, 'main');

?>

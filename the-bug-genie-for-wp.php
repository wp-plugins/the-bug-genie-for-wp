<?php
/*
Plugin Name: The Bug Genie for WP
Plugin URI: http://wordpress.org/extend/plugins/the-bug-genie-for-wp/
Description: Access project and bug information from The Bug Genie.
Version: 1.0
Author: Raphael Reitzig
Author URI: http://lmazy.verrech.net/
License: GPL2
*/
?>
<?php
/*  Copyright 2011 Raphael Reitzig (wordpress@verrech.net)

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

require_once('LmazyPlugin.php');

/**
 * Main class of plugin The Bug Genie for WP. See readme.txt for details.
 *
 * @author Raphael Reitzig
 * @version 1.0
 */
class Tbg4Wp extends LmazyPlugin {

  /**
   * Creates a new instance
   *
   * Registers shortcodes, settings and necessary hooks.
   * @see LmazyPlugin::__construct()
   */
  function __construct() {
    parent::__construct(array('name' => 'the-bug-genie-for-wp',
                              'prettyName' => 'The Bug Genie for WP',
                              'mainFile' => __FILE__,
                              'resources' => array(array('Homepage', 'http://wordpress.org/extend/plugins/the-bug-genie-for-wp/'),
                                                   array('Blog', 'http://lmazy.verrech.net/tag/tbg4wp'),
                                                   array('FAQ', 'http://wordpress.org/extend/plugins/the-bug-genie-for-wp/faq'),
                                                   array('Bugtracker', 'http://bugs.verrech.net/thebuggenie/tbg4wp'),
                                                   array('Support', 'http://wordpress.org/tags/the-bug-genie-for-wp?forum_id=10'),
                                                   array('Contact', 'mailto:wordpress@verrech.net'))));

    // Register Shortcodes
    add_shortcode('issue', array(&$this, 'issue'));
    add_shortcode('issues', array(&$this, 'issues'));
    add_shortcode('reportbug', array(&$this, 'reportbug'));

    // Register settings via Settings API
    include('settings.inc.php');
  }

  /**
   * Called when this plugin is activated.
   * Sets default options if there are no options yet.
   */
  function activate() {
    if ( !get_option($this->name) ) {

      if ( empty($_SERVER['SERVER_NAME']) ) {
        $_SERVER['SERVER_NAME'] = 'localhost';
      }

      $options = array( 'tbg_url' => 'http://'.$_SERVER['SERVER_NAME'].'/bugs');
      add_option($this->name, $options);
    }
  }

  /**
   * Called when this plugin is deactivated.
   * Does nothing.
   */
  function deactivate() {}

  /**
   * This function validates option input.
   * Reports invalid inputs (via `add_settings_error`).
   * @param array $input key/value pairs of wannabe options.
   * @return array a valid option array updated by valid pairs from the
   *               input array
   */
  function options_validate($input) {
    $options = get_option($this->name);

    if ( !empty($input['tbg_url']) && strlen(trim($input['tbg_url'])) > 0 ) {
      $options['tbg_url'] = $input['tbg_url'];
    }
    else {
      add_settings_error('tbg_url', 'empty_setting', 'Empty TBG address.');
    }

    return $options;
  }

  /*******************************************
   *          Functionality below            *
   *******************************************/

  private $cache = array();

  /**
   * Looks up the specified issue.
   * @param string $project The desired issue's project.
   * @param string $issue The desired issue's id.
   * @return mixed An associative array representing the desired issue
   *               or <code>false</code> if it can not be found.
   */
  private function lookupSingle(&$project, &$issue) {
    $result = false;

    if ( empty($this->cache[$project.'/'.$issue]) ) {
      $got = $this->get($this->options['tbg_url'].'/thebuggenie/'.$project.'/issues/'.$issue.'/format/json');
      if ( !empty($got) ) {
        $this->cache[$project.'/'.$issue] = $got;
        $result = $got;
      }
    }
    else {
      $result = $this->cache[$project.'/'.$issue];
    }
    return $result;
  }

  /**
   * Looks up issues from the specified project that match the specified filter
   * criteria.
   * @param string $project The desired issues' project.
   * @param array $issue The desired issue's id.
   * @return mixed An associative array containing the desired list of issues
   *               or <code>false</code> if it can not be found.
   */
  private function lookupList(&$project, &$filters) {
    $result = false;

    $url = $this->options['tbg_url'].'/thebuggenie/'.$project.'/list/issues/json/detailed/yes';
    foreach ( $filters as $k => $v ) {
      $url .= '/'.$k.'/'.urlencode($v);
    }
    $got = $this->get($url);
    if ( !empty($got) ) {
      $result = $got['issues'];
    }
    // TODO put issues to cache (once TBG API does not treat single issues and lists differently)

    return $result;
  }

  /**
   * Retrieves JSON at the specified address, parses it and returns it
   * as associative array.
   * @param string $url Address to pull JSON from
   * @return mixed Array with target JSON's content on success, <code>false</code>
   *               else.
   */
  private function get($url) {
    if ( WP_DEBUG ) { echo '<div class="debugbox">Retrieving JSON from '.$url.'.</div>'; }

    $content = @file_get_contents($url);
    if ( !empty($content) ) {
      $result = @json_decode($content, true);
      if ( !empty($result) ) {
        return $result;
      }
    }
    else {
      if ( WP_DEBUG ) { echo '<div class="errorbox">Cannot read from '.$url.'</div>'; }
    }

    return false;
  }

  /**
   * Called by shortcode 'issue'. Prints the specified issue with its number and
   * title.
   * @param array $args Shortcode parameter array. Must contain appropriate
   *                    values for keys 'project' and 'issue'. If value of
   *                    'title' equals 'yes', issue title is shown.
   * @return string Shortcode replacement text
   */
  function issue($args) {
    foreach ( array('project', 'issue') as $k ) {
      $args[$k] = trim($args[$k]);
      if ( empty($args[$k]) ) {
        return '<div class="errorbox">Specify parameter \''.$k.'\'</div>';
      }
    }

    $titlemode = 'nr';
    if ( !empty($args['title']) && $args['title'] === "yes" ) {
      $titlemode = 'long';
    }

    $issue = $this->lookupSingle($args['project'], $args['issue']);
    if ( empty($issue) ) {
      return '<div class="errorbox">Issue '.$args['issue'].' in project '.$args['project'].' can not be found.</div>';
    }

    return $this->issue2string($args['project'], $issue, $titlemode);
  }

  /**
   * Prints an issue in its long version, namely with number and title.
   * @param string $project Project the printed issue belongs to.
   * @param array $issue Issue details as obtained from JSON
   * @param string $mode One of 'nr', 'long'.
   * @return string If $mode was 'nr', the issue number linking to the issue.
   *                If $mode was 'long', issue number and title linking to the issue.
   */
  private function issue2string(&$project, &$issue, $mode) {
    // Hack needed because TBG API returns status differently for lists than for single issues
    $status =   is_array($issue['status'])
              ? $issue['status']['key']
              : strtolower(preg_replace('/\W/', '', $issue['status']));
    // Hack needed becasue TBG API pollutes issue no for issue lists
    $noparts = preg_split('/\s/', $issue['issue_no']);
    $issue['issue_no'] = $noparts[sizeof($noparts)-1];

    if ( "nr" == $mode ) {
      return '<span title="'.$issue['title'].'" class="issuenr '.($issue['state'] ? 'closed' : 'open').' '.$status.'">[<a href="'.$this->options['tbg_url'].'/thebuggenie/'.$project.'/issues/'.$issue['issue_no'].'">'.$issue['issue_no'].'</a>]</span>';
    }
    elseif ( "long" == $mode ) {
      return '<span title="" class="issue '.($issue['state'] ? 'closed' : 'open').' '.$status.'">[<a href="'.$this->options['tbg_url'].'/thebuggenie/'.$project.'/issues/'.$issue['issue_no'].'">'.$issue['issue_no'].'</a>] '.$issue['title'].'</span>';
    }
    else {
      return '<div class="errorbox">Unrecognized issue printing mode: '.$mode.'</div>';
    }
  }

  /**
   * Called by shortcode 'issues'. Prints the list of specified issues.
   * @param array $args Shortcode parameters. Must contain value for key 'project'.
   *                    Other parameters will be passed to TBG API directly.
   * @return string Shortcode replacement text
   */
  // project key + key/value => issue list described by parameters, class: issues)
  function issues($args) {
    if ( empty($args['project']) ) {
      return '<div class="errorbox">Specify parameter \'project\'</div>';
    }

    $project = $args['project'];
    unset($args['project']);
    $issues = $this->lookupList($project, $args);
    if ( false === $issues ) {
      return '<div class="errorbox">Could not retrieve issues for project '.$project.'.</div>';
    }
    elseif ( empty($issues) ) {
      return '<p class="noissuelist">No such issues in project '.$project.'.</p>';
    }
    else {
      $result = "<ul class=\"issuelist\">\n";
      foreach ( $issues as $issue ) {
        $result .= '  <li>'.$this->issue2string($project, $issue, 'long')."</li>\n";
      }
      $result .= "</ul>\n";
      return $result;
    }
  }

  /**
   * Called by shortcode 'reportbug'. Prints a link to the issue report form.
   * @param array $args Shortcode parameters. Must contain value for key 'project'.
   * @param string $content Link text. Default is 'File a ticket'.
   * @return string Link HTML.
   */
  function reportbug($args, $content=null) {
    if ( empty($args['project']) ) {
      return '<div class="errorbox">Specify parameter \'project\'</div>';
    }

    // This instead of default parameter because WP passes sth for empty content
    if ( empty($content) ) {
      $content = "File a ticket";
    }

    return '<a href="'.$this->options['tbg_url'].'/thebuggenie/'.$args['project'].'/issues/new" class="reportbug" title="">'.$content.'</a>';
  }
}

new Tbg4Wp();
?>

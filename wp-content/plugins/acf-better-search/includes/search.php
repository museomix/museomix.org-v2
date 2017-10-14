<?php

  class SearchACFBetterSearch {

    function __construct() {

      $this->getDatabaseClass();
      $this->loadSettings();

      $this->loadFilters();

    }

    private function getDatabaseClass() {

      global $wpdb;

      $this->wpdb = $wpdb;

    }

    private function loadSettings() {

      if (get_option('acfbs_fields_types') !== false) {

        $this->fieldsTypes = is_array(get_option('acfbs_fields_types')) ? get_option('acfbs_fields_types') : array();

      } else {

        $this->fieldsTypes = array('text', 'textarea', 'wysiwyg');

      }


      if (get_option('acfbs_whole_phrases') !== false) {

        $this->wholePhrases = get_option('acfbs_whole_phrases') ? true : false;

      } else {

        $this->wholePhrases = false;

      }

    }

    private function loadFilters() {

      add_filter('posts_search',  array($this, 'sqlWhere'),    10, 2); 
      add_filter('posts_join',    array($this, 'sqlJoin'),     10, 2);
      add_filter('posts_request', array($this, 'sqlDistinct'), 10, 2); 

    }

    public function sqlWhere($where, $query) {

      if (!isset($query->query_vars['s']) || empty($query->query_vars['s']))
        return $where;


      $acfConditions       = $this->getACFConditions($query->query_vars['s']);
      $wordpressConditions = $this->getDefaultWordPressConditions($query->query_vars['s']);
      
      $where = 'AND (' . $acfConditions . ' OR ' . $wordpressConditions . ')';

      return $where;

    }

    private function getACFConditions($words) {

      if (!$this->fieldsTypes)
        return '(1 = 2)';


      $words           = !$this->wholePhrases ? explode(' ', $words) : array($words);
      $wordsConditions = array();

      foreach ($words as $word) {

        $word = addslashes($word);

        $wordsConditions[] = 'a.meta_value LIKE \'%' . $word . '%\'';

      }

      $wordsConditions = '(' . implode(') AND (', $wordsConditions) . ')';
      $conditions      = '(' . $wordsConditions . ' AND (b.meta_id = a.meta_id + 1) AND (c.post_name = b.meta_value))';

      return $conditions;

    }

    private function getDefaultWordPressConditions($words) {

      $words           = !$this->wholePhrases ? explode(' ', $words) : array($words);
      $wordsConditions = array();

      foreach ($words as $word) {

        $word = addslashes($word);

        $wordConditions   = array();
        $wordConditions[] = '(' . $this->wpdb->posts . '.post_title LIKE \'%' . $word . '%\')';
        $wordConditions[] = '(' . $this->wpdb->posts . '.post_content LIKE \'%' . $word . '%\')';
        $wordConditions[] = '(' . $this->wpdb->posts . '.post_excerpt LIKE \'%' . $word . '%\')';

        $wordsConditions[] = '(' . implode(' OR ', $wordConditions) . ')';

      }

      if (count($wordsConditions) > 1) {

        $conditions = '(' . implode(' AND ', $wordsConditions) . ')';

      } else {

        $conditions = $wordsConditions[0];

      }

      return $conditions;

    }

    public function sqlJoin($join, $query) {

      if (empty($query->query_vars['s']))
        return $join;

      if (!$this->fieldsTypes)
        return $join;


      $parts   = array();
      $parts[] = 'LEFT JOIN ' . $this->wpdb->postmeta . ' AS a ON (a.post_id = ' . $this->wpdb->posts . '.ID)';
      $parts[] = 'LEFT JOIN ' . $this->wpdb->postmeta . ' AS b ON (b.post_id = ' . $this->wpdb->posts . '.ID)';
      $parts[] = 'LEFT JOIN ' . $this->wpdb->posts . ' AS c ON ' . $this->getFieldsTypesConditions();
      $join   .= implode(' ', $parts);

      return $join;

    }

    private function getFieldsTypesConditions() {

      $typesConditions  = array();
      $conditions       = array();

      foreach ($this->fieldsTypes as $type) {

        $typesConditions[] = '(c.post_content LIKE \'%:"' . $type. '"%\')';

      }

      $conditions[] = '(c.post_type = \'acf-field\')';

      if (count($typesConditions) > 1) {

        $conditions[] = '(' . implode(' OR ', $typesConditions) . ')';

      } else {

        $conditions[] = $typesConditions[0];

      }
      
      $conditions = '(' . implode(' AND ', $conditions) . ')';

      return $conditions;

    }

    public function sqlDistinct($sql, $query) {

      if (empty($query->query_vars['s']))
        return $sql;


      $sql = str_replace('SELECT', 'SELECT DISTINCT', $sql);

      return $sql;

    }

  }
<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Ratings\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;


/**
 * CakePHP Ratings Plugin
 *
 * Rating fixture
 *
 * @package 	ratings
 * @subpackage 	ratings.tests.fixtures
 */
class RatingsFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 36],
		'user_id' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 36],
		'foreign_key' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 36],
		'model' => ['type' => 'string', 'null' => false, 'default' => null],
		'value' => ['type' => 'float', 'null' => true, 'default' => '0', 'length' => '8,4'],
		'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
		'_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']], 'UNIQUE_RATING' => ['type' => 'unique', 'columns' => ['user_id', 'foreign_key', 'model']]]
	);

/**
 * Records
 *
 * @var array
 * @access public
 */
	public $records = array(
		array(
			'id' => 1,
			'user_id' => '1',
			'foreign_key' => '1', // first article
			'model' => 'Article',
			'value' => 1,
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12'),
		array(
			'id' => 2,
			'user_id' => '1',
			'foreign_key' => '1', // first post
			'model' => 'Post',
			'value' => 1,
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12'),
		array(
			'id' => 3,
			'user_id' => '1',
			'foreign_key' => '2', // second post
			'model' => 'Post',
			'value' => 3,
			'created' => '2009-01-01 12:12:12',
			'modified' => '2009-01-01 12:12:12'));
}
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
namespace Ratings\Test\TestCase\Controller\Component;

use App\Controller\Component\Auth;
use Cake\Network\Session;
use Cake\Controller\Controller;
use Ratings\Controller\Component\RatingsComponent;
use Cake\TestSuite\TestCase;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;

/**
 * Test ArticlesTestController
 *
 * @package ratings
 * @subpackage ratings.tests.cases.components
 */
class ArticlesTestController extends Controller {

/**
 * Models used
 *
 * @var array
 */
	public $modelClass = 'Articles';

/**
 * Helpers used
 *
 * @var array
 */
	public $helpers = array('Html', 'Form');

/**
 * Components used
 *
 * @var array
 */
	public $components = array('Ratings.Ratings', 'Auth', 'Flash');

/**
 * test method
 *
 * @return void
 */
	public function test() {
		return;
	}

/**
 * Overloaded redirect
 *
 * @param string $url
 * @param string $status
 * @param string $exit
 * @return void
 */
	public function redirect($url, $status = null) {
		$this->redirect = $url;
	}
}

/**
 * Test RatingsComponentTest
 *
 * @package ratings
 * @subpackage ratings.tests.cases.components
 */
class RatingsComponentTest extends TestCase {

/**
 * Controller using the tested component
 *
 * @var Controller
 */
	public $Controller;

/**
 * Mock AuthComponent object
 *
 * @var MockAuthComponent
 */
	public $AuthComponent;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'core.cake_sessions',
		'plugin.ratings.ratings',
		'plugin.ratings.articles',
		'plugin.ratings.users'
	);

/**
 * startTest method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->session = new Session();

		$this->session->write('foo', 'bar');
		$this->session->delete('foo');

		$this->Controller = new ArticlesTestController(new Request());
		//$this->Controller->constructClasses();

		//$this->Collection = $this->getMock('ComponentRegistry');

		/*
		if (!class_exists('MockAuthComponent')) {
 			$this->getMock('AuthComponent', array('user'), array($this->Collection), "MockAuthComponent");
		}

		$this->AuthComponent = new MockAuthComponent($this->Collection);
		$this->AuthComponent->enabled = true;
		$this->Controller->Auth = $this->AuthComponent;
		*/
	}

/**
 * endTest method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		$this->session->destroy();
		unset($this->Controller);
		TableRegistry::clear();
	}

/**
 * testInitialize
 *
 * @return void
 */
	public function testInitialize() {
		$this->_initControllerAndRatings(array(), false);
		$this->assertEquals(array('Html' => null, 'Form' => null, 'Ratings.Rating'), $this->Controller->helpers);
		$this->assertTrue($this->Controller->Article->Behaviors->loaded('Ratable'), 'Ratable behavior should attached.');
		$this->assertEquals('Articles', $this->Controller->Ratings->modelName);
	}

/**
 * testInitializeWithParamsForBehavior
 *
 * @return void
 */
	public function testInitializeWithParamsForBehavior() {
		$this->Controller->components = array(
			'Ratings.Ratings' => array(
				'update' => true),
			'Auth');

		$this->_initControllerAndRatings(array(), false);
		$this->assertEquals(array(
			'Html' => null, 'Form' => null, 'Ratings.Rating'), $this->Controller->helpers);
		$this->assertTrue($this->Controller->Article->Behaviors->loaded('Ratable'), 'Ratable behavior should attached.');
		$this->assertTrue($this->Controller->Article->Behaviors->Ratable->settings['Article']['update'], 'Ratable behavior should be updatable.');
		$this->assertEquals('Articles', $this->Controller->Ratings->modelName);
	}

/**
 * testInitializeWithParamsForComponent
 *
 * @return void
 */
	public function testInitializeWithParamsForComponent() {
		$this->Controller->components = array(
			'Ratings.Ratings' => array(
				'actionNames' => array('show')),
			'Auth');

		$this->_initControllerAndRatings(array(), false);
		$this->assertEquals(array('Html' => null, 'Form' => null, 'Ratings.Rating'), $this->Controller->helpers);
		$this->assertTrue($this->Controller->Article->Behaviors->loaded('Ratable'), 'Ratable behavior should attached.');
		$this->assertEquals(array('show'), $this->Controller->Ratings->actionNames);
		$this->assertEquals('Articles', $this->Controller->Ratings->modelName);
	}

/**
 * testStartup
 *
 * @return void
 */
	public function testStartup() {
		/*
		$this->AuthComponent
			->expects($this->any())
			->method('user')
			->with('id')
			->will($this->returnValue(array('1')));
		*/

		$params = array(
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => array(),
			'?' => array(
				'rating' => '5',
				'rate' => '2',
				'redirect' => true));
		$expectedRedirect = array(
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test');
/*
		$this->Controller->Session->expectCallCount('setFlash', 3);

		$this->Controller->Session->expectAt(0, 'setFlash', array('Your rate was successfull.', 'default', array(), 'success'));
		$this->Controller->Session->expectAt(1, 'setFlash', array('You have already rated.', 'default', array(), 'error'));
		$this->Controller->Session->expectAt(2, 'setFlash', array('Invalid rate.', 'default', array(), 'error'));
*/
//		$this->Controller->Session->write('Message', null);
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);

//		$this->Controller->Session->write('Message', null);
		$params['?']['rate'] = '1';
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);

//		$this->Controller->Session->write('Message', null);
		$params['?']['rate'] = 'invalid-record!';
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);
	}

/**
 * testStartupAcceptPost
 *
 * @return void
 */
	public function testStartupAcceptPost() {
		/*
		$this->AuthComponent
			->expects($this->any())
			->method('user')
			->with('id')
			->will($this->returnValue(1));
		*/

		$params = array(
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => array(),
			'?' => array(
				'rate' => '2',
				'redirect' => true));
		$expectedRedirect = array(
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test');
		$this->Controller->data = array('rating' => 2);

		//$this->Controller->Session->write('Message', null);

		//$this->Controller->Session->expects($this->any())->method('setFlash');
		$this->_initControllerAndRatings($params);
		$this->assertEquals($expectedRedirect, $this->Controller->redirect);
	}

/**
 * testBuildUrl
 *
 * @return void
 */
	public function testBuildUrl() {
		$params = array(
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'pass' => array(),
			'?' => array(
				'foo' => 'bar',
				'rating' => 'test',
				'rate' => '5',
				'redirect' => true));
		$this->_initControllerAndRatings($params);

		$result = $this->Controller->Ratings->buildUrl();
		$expected = array(
			'plugin' => null,
			'controller' => 'Articles',
			'action' => 'test',
			'?' => array('foo' => 'bar')
		);
		$this->assertEquals($expected, $result);
	}

/**
 * Convenience method for testing: Initializes the controller and the Ratings component
 *
 * @param array $params Controller params
 * @param boolean $doStartup Whether or not startup has to be called on the Ratings Component
 * @return void
 */
	protected function _initControllerAndRatings($params = array(), $doStartup = true) {
		$_default = array('?' => array(), 'pass' => array());
		$this->Controller->request->params = array_merge($_default, $params);
		if (!empty($this->Controller->request->params['?'])) {
			$this->Controller->request->query = $this->Controller->request->params['?'];
		}

		$this->Controller->components()->unload('Ratings');
		$this->Controller->loadComponent('Ratings.Ratings');
		$event = new Event('beforeFilter', $this->Controller);
		$this->Controller->Ratings->beforeFilter($event);
		//$this->Controller->Components->trigger('initialize', array(&$this->Controller));
		//$this->Controller->Auth = $this->AuthComponent;
		//$this->Controller->Ratings->beforeFilter($this->Controller);
	}

}

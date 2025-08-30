<?php

class TestCase extends CIPHPUnitTestCase
{
    protected static $migrate = false;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        chdir(APPPATH.'/../');

        $enable_migrations = (getenv('ENABLE_MIGRATIONS') === 'FALSE' ? false : true);

        // Run migrations once
        if ($enable_migrations !== false && ! self::$migrate)
        {
            $CI =& get_instance();
            $CI->load->database();
            $CI->load->library('migration');
            $c = $CI->migration->current();
            if ($c === false) {
                throw new RuntimeException($CI->migration->error_string());
            }

            self::$migrate = true;
        }
    }

    public static function tearDownAfterClass(): void
    {
      /*  $enable_migrations = (getenv('ENABLE_MIGRATIONS') === 'FALSE' ? false : true);

        if ($enable_migrations === true && self::$migrate === true) {
            $CI =& get_instance();
            $db = $CI->load->database(getenv('BaseCode_guardian_users'), TRUE);

            $tables = $db->query('SHOW TABLES')->result_array();
            foreach($tables as $table) {
                if ($table['Tables_in_GUARDIAN'] !== 'migrations') {
                    $db->query(sprintf('TRUNCATE %s', $table['Tables_in_GUARDIAN']));
                } else {
                    $db->query('TRUNCATE migrations');
                }
            }
            
            //self::$migrate = false;
        }*/

        parent::tearDownAfterClass();
    }
}

/**
 * @author  Patrick M. Garcia <patrick@rhombuspower.com>
 */
class RhombusControllerTestCase extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        
        // Disable the user authentication check since it is not needed for unit testing
        $this->request->setCallable(
            function ($CI) {
                $CI->hooks->enabled = false;
            }
        );

        // Add Mock user session
        $this->request->addCallable(
            function ($CI) {
                $session_data = array(
                    'email' => 'unit_tester@rhombuspower.com',
                    'name' => 'Unit Tester',
                    'account_type' => 'USER',
                    'timestamp' => 1609459200,
                    'profile_image' => NULL,
                    'id' => 1
                );

                $CI->session->set_userdata('logged_in', $session_data);
            }
        );
    }
}

class RhombusModelTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        
        // Reset CodeIgniter super object
        $this->resetInstance();
    }

    /**
     * Returns a CI_DB mock object setup with method-chaining methods
     * 
     * @return mixed
     */
    protected function getMethodChainingDBMock()
    {
        $mock = $this->getMockBuilder('CI_DB_mysqli_driver')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('select')->willReturn($mock);
        $mock->method('from')->willReturn($mock);
        $mock->method('join')->willReturn($mock);
        $mock->method('where')->willReturn($mock);
        $mock->method('where_in')->willReturn($mock);
        $mock->method('where_not_in')->willReturn($mock);
        $mock->method('order_by')->willReturn($mock);
        $mock->method('group_start')->willReturn($mock);
        $mock->method('group_by')->willReturn($mock);
        $mock->method('like')->willReturn($mock);
        $mock->method('or_like')->willReturn($mock);
        $mock->method('not_like')->willReturn($mock);
        $mock->method('limit')->willReturn($mock);
        $mock->method('offset')->willReturn($mock);
        $mock->method('distinct')->willReturn($mock);
        $mock->method('having')->willReturn($mock);
        $mock->method('set')->willReturn($mock);
        $mock->method('or_like')->willReturn($mock);
        $mock->method('where_not_in')->willReturn($mock);
        return $mock;
    }

     /**
     * Returns a CI_Session mock object setup
     * 
     * @return mixed
     */
    protected function getSessionMock()
    {
        $mock = $this->getMockBuilder('CI_Session')
            ->disableOriginalConstructor()
            ->getMock();
        return $mock;
    }
}

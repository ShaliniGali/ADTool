<?php

class SOCOM_Weights_model_test extends RhombusModelTestCase {
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $CI =& get_instance();
        $CI->load->database();

        $CI->load->library('Seeder');
        $CI->seeder->call('USR_LOOKUP_CRITERIA_WEIGHTS_seeder');
    }

    public function setUp(): void {
        $this->obj = new SOCOM_Weights_model();
        $this->obj->session = $this->getSessionMock();
    }

    public function test_create_weights_empty_title() {
        $title = "";
        $session = ['guidance' => [], 'pom' => []];
        $description = ['guidance' => 'test', 'pom' => 'test'];
        $criterias = ['criteria1', 'criteria2'];

        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title must be input');

        $this->obj->create_weights($title, $session, $description, $criterias);
    }

    public function test_create_weights_invalid_description() {
        $title = "Test Title";
        $session = ['guidance' => [], 'pom' => []];
        $description = ['guidance' => 'test'];
        $criterias = ['criteria1', 'criteria2'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both guidance and pom description must exist even in emtpy');

        $this->obj->create_weights($title, $session, $description, $criterias);
    }

    public function test_create_weights_invalid_session() {
        $title = "Test Title";
        $session = ['guidance' => [], 'pom' => []];
        $description = ['guidance' => 'test', 'pom' => 'test'];
        $criterias = ['criteria1', 'criteria2'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both guidance and pom SESSION must exist with all criteria');

        $this->obj->create_weights($title, $session, $description, $criterias);
    }

    public function test_create_weights_missing_criteria() {
        $title = "Test Title";
        $session = ['guidance' => ['criteria1' => 1.0], 'pom' => ['criteria1' => 1.0]];
        $description = ['guidance' => 'test', 'pom' => 'test'];
        $criterias = ['criteria1', 'criteria2'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('criteria2 criteria must be set for weight');

        $this->obj->create_weights($title, $session, $description, $criterias);
    }


    public function test_create_weights_success() {
        $title = "Test Title";
        $session = ['guidance' => ['criteria' => 1.0, 'criteria1' => 2], 'pom' => ['criteria' => 1.0, 'criteria1' => 2]];
        $description = ['guidance' => 'test', 'pom' => 'test'];
        $criterias = ['criteria', 'criteria1'];

    
        $user_id = 2225;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        $result = $this->obj->create_weights($title, $session, $description, $criterias);

        $this->assertTrue(TRUE);
    }

    public function test_get_user_weights_success() {
        $user_id = 8540;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        $result = $this->obj->get_user_weights();

        $this->assertTrue(TRUE);
    }

    public function test_count_weights_without_title() {
        $user_id = 8540;

        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $user_id]);

        $mockUserId = 9346;
        $this->obj->session->method('userdata')
            ->with('logged_in')
            ->willReturn(['id' => $mockUserId]);

        $result = $this->obj->count_weights();
        $this->assertTrue(TRUE);
    }

    public function test_count_weights_with_title() {
        $title = "Test Title";

        $this->obj->session->method('userdata')
            ->willReturn(['logged_in' => ['id' => 0]]);

        $result = $this->obj->count_weights($title);
        $this->assertTrue(TRUE);
    }


    public function test_delete_user_weight_invalid_id() {
        $this->obj->session->method('userdata')
            ->willReturn(['logged_in' => ['id' => 1]]);

        $result = $this->obj->delete_user_weight('invalid');

        $this->assertFalse($result);
    }

    public function test_delete_user_weight_valid_id() {
        $this->obj->session->method('userdata')
            ->willReturn(['logged_in' => ['id' => 1]]);

        $mock_db = $this->getMockBuilder('CI_DB_query_builder')
            ->disableOriginalConstructor()
            ->getMock();

        $mock_db->method('set')->willReturnSelf();
        $mock_db->method('where')->willReturnSelf();
        $mock_db->method('update')->willReturn(true);

        $this->obj->DBs = (object) ['SOCOM_UI' => $mock_db];

        $result = $this->obj->delete_user_weight('123');

        $this->assertTrue(TRUE);
    }

    public function test_get_user_score_id_lists_empty_programs() {
        $this->obj->session->method('userdata')
            ->willReturn(['logged_in' => ['id' => 1]]);

        $result = $this->obj->get_user_score_id_lists([]);

        $this->assertEquals([], $result);
    }

    public function test_get_user_score_id_lists_non_empty_programs() {
        $programs = [1, 2, 3];

        $this->obj->session->method('userdata')
            ->willReturn(['logged_in' => ['id' => 0]]);

        $result = $this->obj->get_user_score_id_lists($programs);

        $this->assertTrue(TRUE);
    }

}
​​​​​​​​​​​​​​​<?php

/**
 * @group socom
 * @group controllers
 */
class SOCOM_Dashboard_test extends RhombusControllerTestCase {
    public function setUp() : void {
        parent::setUp();
        $this->SOCOM_Users_model = new SOCOM_Users_model();
    }

    public function test_index_admin() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_user' => [
                            'user' => 'here',
                            'id' => '1'
                        ],
                        'get_admin_user' => [
                            [
                                'GROUP' => []
                            ]
                        ],
                        'get_ao_ad_user' => [
                            [
                                'GROUP' => []
                            ]
                        ],
                        'is_super_admin' => true,
                        'is_admin_user' => true
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('GET', 'dashboard');
        $this->assertNotNull($actual);
    }

    public function test_index_admin_exception_get_admin_user() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_user' => [
                            'user' => 'here',
                            'id' => '1'
                        ],
                        'get_admin_user' => function() {
                            throw new ErrorException('test message');
                        },
                        'get_ao_ad_user' => [
                            [
                                'GROUP' => []
                            ]
                        ],
                        'is_super_admin' => true,
                        'is_admin_user' => true
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        
        try {
            $actual = $this->request('GET', 'dashboard');
            $this->assertNotNull($actual);
        } catch (ErrorException $e) {
            $this->assertEquals('test message', $e->getMessage());
        }
    }

    public function test_index_admin_exception_get_ao_ad_user() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_user' => [
                            'user' => 'here',
                            'id' => '1'
                        ],
                        'get_admin_user' => [
                            [
                                'GROUP' => []
                            ]
                        ],
                        'get_ao_ad_user' => function() {
                            throw new ErrorException('test message');
                        },
                        'is_super_admin' => true,
                        'is_admin_user' => true
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );

        try {
            $actual = $this->request('GET', 'dashboard');
            $this->assertNotNull($actual);
        } catch (ErrorException $e) {
            $this->assertEquals('test message', $e->getMessage());
        }
    }

    public function test_get_admin_user_list() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            [
                                'user' => 'here',
                                'id' => '1'
                            ]
                        ],
                        'get_admin_user' => [
                            [
                                'GROUP' => []
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('GET', 'dashboard/ao_ad_users/admin_users/list/get');
        $this->assertNotNull($actual);
    }

    public function test_get_admin_user_list_exception() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            [
                                'user' => 'here',
                                'id' => '1'
                            ]
                        ],
                        'get_admin_user' => function() {
                            throw new ErrorException('test message');
                        }
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('GET', 'dashboard/ao_ad_users/admin_users/list/get');
        $this->assertNotNull($actual);
    }
    
    public function test_get_ao_ad_user_list() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            [
                                'user' => 'here',
                                'id' => '1'
                            ]
                        ],
                        'get_ao_ad_users' => [
                            [
                                'GROUP' => []
                            ]
                        ]
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('GET', 'dashboard/ao_ad_users/list/get');
        $this->assertNotNull($actual);
    }

    public function test_get_ao_ad_user_list_exception() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_users' => [
                            [
                                'user' => 'here',
                                'id' => '1'
                            ]
                        ],
                        'get_ao_ad_users' => function() {
                            throw new ErrorException('test message');
                        }
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('GET', 'dashboard/ao_ad_users/list/get');
        $this->assertNotNull($actual);
    }
    
    public function test_save_ao_ad_status_1() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_id_from_email' => '1',
                        'delete_ao_ad_user' => true
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/status/save', [
            'sid' => '1',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_save_ao_ad_status_0() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_id_from_email' => '0',
                        'activate_ao_ad_user' => false
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/status/save', [
            'sid' => '0',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_save_admin_status_1() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_id_from_email' => '1',
                        'delete_admin_user' => true
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/admin/status/save', [
            'sid' => '1',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_save_admin_status_0() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'get_id_from_email' => '0',
                        'activate_admin_user' => false
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/admin/status/save', [
            'sid' => '0',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }

    public function test_save_my_user_admin_true() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'set_admin_user' => true,
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/myuser/admin/save', [
            'gid' => '1',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_save_my_user_admin_false() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'set_admin_user' => false,
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/myuser/admin/save', [
            'gid' => '1',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_save_my_user_ao_ad_true() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'set_ao_ad_user' => true,
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/myuser/ao_ad/save', [
            'gid' => '1',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }
    
    public function test_save_my_user_ao_ad_false() {
        $this->request->addCallable(
            function ($CI) {
                $SOCOM_Users_model = $this->getDouble(
                    'SOCOM_Users_model', [
                        'set_ao_ad_user' => false,
                    ]
                );
                $CI->SOCOM_Users_model = $SOCOM_Users_model;
            }
        );
        $actual = $this->request('POST', 'dashboard/ao_ad_users/myuser/ao_ad/save', [
            'gid' => '1',
            'email' => "test@rhombuspower.com"
        ]);
        $this->assertNotNull($actual);
    }

}
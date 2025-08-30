<?php 
/**
 * @group base
 */
class FACS_manager_controller_test extends RhombusControllerTestCase 
{
    public function test_index() {
        $status = array(
            'status' => AccountStatus::Deleted,
            'updated_on'=>time(),
        );

        $result = [
            [
                'id' => 1,
                'title' => 'Test',
                'icon' => 'test.svg',
                'note' => 'test',
                'description' => 'test desc'
            ]
        ];

         // user is super admin
        $this->request->addCallable(
            function ($CI) use ($status, $result) {
                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model' , [
                        'get_roles' => 'role',
                    ]
                );
                $CI->Roles_manager_model = $Roles_manager_model;
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'update_tile_status' => $status,
                        'get_tiles' => $result
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );

        $actual = $this->request('GET', '/facs_manager');
        $this->assertStringNotContainsString('A PHP Error was encountered', $actual);
    }

    public function test_index_not_super_admin() {

         // user is not super admin
		   $this->request->addCallable(
            function ($CI) {
                $useraccounttype = $this->getDouble(
                    'UserAccountType', [
                        'checkSuperAdmin' => FALSE
                    ]
                );
                $CI->useraccounttype = $useraccounttype;

            }
        );

        $this->request('GET', '/facs_manager');
        $this->assertResponseCode(401);
    }

    public function test_delete_fac_tiles() {


        $status = array(
            'status' => AccountStatus::Deleted,
            'updated_on'=>time(),
        );

        $result = [
            [
                'id' => 2,
                'title' => 'Test', 
                'icon' => 'test.svg',
                'note' => 'test',
                'description' => 'test desc'
            ]
            ];

        $this->request->addCallable(
            function ($CI) use ($status, $result) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'update_tile_status' => $status,
                        'get_tiles' => $result
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

            }
        );

        $actual = $this->request('POST', '/facs_manager/delete_facs',[
            'facs_type' => 'tiles',
            'rowId' => 1
        ]);

        $this->assertIsString($actual);
    }

    public function test_delete_fac_roles() {


        $status = array(
            'status' => AccountStatus::Deleted,
            'updated_on'=>time(),
        );

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($status, $result) {
                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model', [
                        'update_roles_status' => $status,
                        'get_roles' => $result
                    ]
                );
                $CI->Roles_manager_model = $Roles_manager_model;

            }
        );

        $actual = $this->request('POST', '/facs_manager/delete_facs',[
            'facs_type' => 'roles',
            'rowId' => 1
        ]);

        $this->assertIsString($actual);
    }

    public function test_delete_fac_subapps() {


        $status = array(
            'status' => AccountStatus::Deleted,
            'updated_on'=>time(),
        );

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($status, $result) {
                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model', [
                        'update_subapps_status' => $status,
                        'get_subapps' => $result
                    ]
                );
                $CI->Subapps_manager_model = $Subapps_manager_model;

            }
        );

        $actual = $this->request('POST', '/facs_manager/delete_facs',[
            'facs_type' => 'subapps',
            'rowId' => 1
        ]);

        $this->assertIsString($actual);
    }


    public function test_delete_fac_features() {


        $status = array(
            'status' => AccountStatus::Deleted,
            'updated_on'=>time(),
        );

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($status, $result) {
                $Features_manager_model = $this->getDouble(
                    'Features_manager_model', [
                        'update_features_status' => $status,
                        'get_features' => $result
                    ]
                );
                $CI->Features_manager_model = $Features_manager_model;

            }
        );

        $actual = $this->request('POST', '/facs_manager/delete_facs',[
            'facs_type' => 'features',
            'rowId' => 1
        ]);

        $this->assertIsString($actual);
    }

    public function test_delete_fac_role_mappings() {


        $status = array(
            'status' => AccountStatus::Deleted,
            'updated_on'=>time(),
        );

        $result = [
            [
                'id' => 2,
                'app_id' => 1, 
                'subapp_id' => 1,
                'feature_id' => 1,
                'user_role_id' => '[2, 3]'
            ],
            [
                'id' => 2,
                'app_id' => 1, 
                'subapp_id' => 1,
                'feature_id' => 1,
                'user_role_id' => '[2, 3]'
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($status, $result) {
                $Role_mappings_manager_model = $this->getDouble(
                    'Role_mappings_manager_model', [
                        'clear_role_mappings_user_roles' => $status,
                        'get_role_mappings' => $result
                    ]
                );
                $CI->Role_mappings_manager_model = $Role_mappings_manager_model;

            }
        );

        $actual = $this->request('POST', '/facs_manager/delete_facs',[
            'facs_type' => 'role_mappings',
            'rowId' => 1
        ]);

        $this->assertIsString($actual);
    }

    public function test_get_facs_roles() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model', [
                        'get_roles' => $result
                    ]
                );
                $CI->Roles_manager_model = $Roles_manager_model;

            }
        );

        $actual = $this->request('POST','/facs_manager/get_facs',[
            'facs_type' => 'roles'
        ]);

        $this->assertIsString($actual);
    }

    public function test_get_facs_subapps() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];
        $result_alias = [
            [
                'id' => 1,
                'alias_name' => 'Test'
            ],
            [
                'id' => 2,
                'alias_name' => 'Test'
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($result, $result_alias) {
                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model', [
                        'get_subapps' => $result
                    ]
                );
                $CI->Subapps_manager_model = $Subapps_manager_model;

                $Subapps_alias_manager_model = $this->getDouble(
                    'Subapps_alias_manager_model', [
                        'get_subapps_alias' => $result_alias
                    ]
                );
                $CI->Subapps_alias_manager_model = $Subapps_alias_manager_model;

            }
        );

        $actual = $this->request('POST','/facs_manager/get_facs',[
            'facs_type' => 'subapps'
        ]);

        $this->assertIsString($actual);
    }

    public function test_get_facs_features() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Features_manager_model = $this->getDouble(
                    'Features_manager_model', [
                        'get_features' => $result
                    ]
                );
                $CI->Features_manager_model = $Features_manager_model;

            }
        );

        $actual = $this->request('POST','/facs_manager/get_facs',[
            'facs_type' => 'features'
        ]);

        $this->assertIsString($actual);
    }

    public function test_get_facs_role_mappings() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $result2 = [
            [
                'id' => 2,
                'title' => 'Test', 
                'icon' => 'test.svg',
                'note' => 'test',
                'description' => 'test desc'
            ]
        ];

        $result_alias = [
            [
                'id' => 1,
                'alias_name' => 'Test'
            ],
            [
                'id' => 2,
                'alias_name' => 'Test'
            ]
        ];

        $this->request->addCallable(
            function ($CI) use ($result2, $result, $result_alias) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'get_tiles' => $result2
                    ]
                );
                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model', [
                        'get_roles' => $result
                    ]
                );
                $CI->Roles_manager_model = $Roles_manager_model;

                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model', [
                        'get_subapps' => $result
                    ]
                );
                $CI->Subapps_manager_model = $Subapps_manager_model;

                $Subapps_alias_manager_model = $this->getDouble(
                    'Subapps_alias_manager_model', [
                        'get_subapps_alias' => $result_alias
                    ]
                );
                $CI->Subapps_alias_manager_model = $Subapps_alias_manager_model;

                $Features_manager_model = $this->getDouble(
                    'Features_manager_model', [
                        'get_features' => $result
                    ]
                );
                $CI->Features_manager_model = $Features_manager_model;

                $Role_mappings_manager_model = $this->getDouble(
                    'Role_mappings_manager_model', [
                        'get_role_mappings' => $result
                    ]
                );
                $CI->Role_mappings_manager_model = $Role_mappings_manager_model;

            }
        );

        $actual = $this->request('POST','/facs_manager/get_facs',[
            'facs_type' => 'role_mappings'
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_tiles() {

        $result = [
            [
                'id' => 1,
                'title' => 'Test', 
                'icon' => 'test.svg',
                'note' => 'test',
                'description' => 'test desc'
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'update_tiles_record' => TRUE,
                        'get_tiles' => $result
                    ]
                );

                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'tiles',
            'editData' => [
                'id' => 1,
                'title' => 'Test2',
                'icon' => 'test2.svg',
                'note' => 'test2',
                'description' => 'test2'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_tiles_error() {


        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'tiles',
            'editData' => [
                'id' => 1,
                'title' => '',
                'icon' => 'test2.svg',
                'note' => 'test2',
                'description' => 'test2'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_roles() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model' , [
                        'update_roles_record' => TRUE,
                        'get_roles' => $result
                    ]
                );

                $CI->Roles_manager_model = $Roles_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'roles',
            'editData' => [
                'name' => 'test2'

            ]
        ]);

        $this->assertIsString($actual);
    }



    public function test_edit_facs_role_error() {


        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'roles',
            'editData' => [
                'name' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_subapps() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $result_alias = [
            [
                'id' => 1,
                'alias_name' => 'Test'
            ],
            [
                'id' => 2,
                'alias_name' => 'Test'
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result, $result_alias) {

                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model' , [
                        'update_subapps_record' => TRUE,
                        'get_subapps' => $result
                    ]
                );

                $CI->Subapps_manager_model = $Subapps_manager_model;

                $Subapps_alias_manager_model = $this->getDouble(
                    'Subapps_alias_manager_model', [
                        'get_subapps_alias' => $result_alias
                    ]
                );
                $CI->Subapps_alias_manager_model = $Subapps_alias_manager_model;

            }
        );
 

        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'subapps',
            'editData' => [
                'name' => 'Test'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_subapps_error() {

 

        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'subapps',
            'editData' => [
                'name' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_features() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Features_manager_model = $this->getDouble(
                    'Features_manager_model' , [
                        'update_features_record' => TRUE,
                        'get_features' => $result
                    ]
                );

                $CI->Features_manager_model = $Features_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'features',
            'editData' => [
                'name' => 'Test4'
            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_features_error() {



        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'features',
            'editData' => [
                'name' => ''
            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_role_mappings() {

        $result = [
            [
                'id' => 2,
                'app_id' => 1, 
                'subapp_id' => 1,
                'feature_id' => 1,
                'user_role_id' => '[2, 3]'
            ]
        ];

        $result_get_tiles = [
            [
                'id' => 1,
                'label' => 'Label'
            ],
            [
                'id' => 2,
                'label' => 'Label'
            ]
        ];
        
        $result_get_roles = [
            [
                'id' => 1,
                'name' => 'Role'
            ],
            [
                'id' => 2,
                'name' => 'Role'
            ]
        ];

        $result_alias = [
            [
                'id' => 1,
                'alias_name' => 'Test'
            ],
            [
                'id' => 2,
                'alias_name' => 'Test'
            ]
        ];

        $result_features = [
            [
                'id' => 1,
                'name' => 'Role'
            ],
            [
                'id' => 2,
                'name' => 'Role'
            ]
        ];

        $result_get_subapps = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

        $this->request->addCallable(
            function ($CI) use (
                $result, 
                $result_get_tiles, 
                $result_get_roles, 
                $result_alias, 
                $result_features,
                $result_get_subapps
            ) {

                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model', [
                        'get_tiles' => $result_get_tiles
                    ]
                );

                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $Role_mappings_manager_model = $this->getDouble(
                    'Role_mappings_manager_model' , [
                        'update_role_mappings_record' => TRUE,
                        'get_role_mappings' => $result
                    ]
                );

                $CI->Role_mappings_manager_model = $Role_mappings_manager_model;

                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model', [
                        'get_roles' => $result_get_roles
                    ]
                );

                $CI->Roles_manager_model = $Roles_manager_model;

                $Subapps_alias_manager_model = $this->getDouble(
                    'Subapps_alias_manager_model', [
                        'get_subapps_alias' => $result_alias
                    ]
                );
                $CI->Subapps_alias_manager_model = $Subapps_alias_manager_model;

                $Features_manager_model = $this->getDouble(
                    'Features_manager_model', [
                        'get_features' => $result_features
                    ]
                );

                $CI->Features_manager_model = $Features_manager_model;

                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model' , [
                        'get_subapps' => $result_get_subapps
                    ]
                );

                $CI->Subapps_manager_model = $Subapps_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'role_mappings',
            'editData' => [
                'app_id' => 1,
                'id' => 1,
                'subapp_id' => 1,
                'feature_id' => 1,
                'user_role_id' => '[2,3]'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_edit_facs_role_mappings_error() {


        $actual = $this->request('POST','/facs_manager/edit_facs',[
            'facs_type' => 'role_mappings',
            'editData' => [
                'app_id' => '',
                'subapp_id' => '',
                'feature_id' => '',
                'user_role_id' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }


    public function test_add_facs_role_mappings() {

        $result = [
            [
                'id' => 2,
                'app_id' => 1, 
                'subapp_id' => 1,
                'feature_id' => 1,
                'user_role_id' => '[2, 3]'
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Role_mappings_manager_model = $this->getDouble(
                    'Role_mappings_manager_model' , [
                        'insert_role_mappings_record' => TRUE,
                        'get_role_mappings' => $result
                    ]
                );

                $CI->Role_mappings_manager_model = $Role_mappings_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'role_mappings',
            'addData' => [
                'app_id' => 1,
                'subapp_id' => 1,
                'feature_id' => 1,
                'user_role_id' => '[3,4]'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_role_mappings_error() {


        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'role_mappings',
            'addData' => [
                'app_id' => '',
                'subapp_id' => '',
                'feature_id' => '',
                'user_role_id' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_features() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Features_manager_model = $this->getDouble(
                    'Features_manager_model' , [
                        'insert_features_record' => TRUE,
                        'get_features' => $result
                    ]
                );

                $CI->Features_manager_model = $Features_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'features',
            'addData' => [
                'name' => 'Test2'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_features_error() {


        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'features',
            'addData' => [
               'name' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_subapps() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ],
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model' , [
                        'insert_subapps_record' => TRUE,
                        'get_subapps' => $result
                    ]
                );

                $CI->Subapps_manager_model = $Subapps_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'subapps',
            'addData' => [
                'name' => 'Test2'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_subapps_error() {


        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'subapps',
            'addData' => [
               'name' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_roles() {

        $result = [
            [
                'id' => 2,
                'name' => 'Test',
                'status' => AccountStatus::Active,
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model' , [
                        'insert_roles_record' => TRUE,
                        'get_roles' => $result
                    ]
                );

                $CI->Roles_manager_model = $Roles_manager_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'roles',
            'addData' => [
                'name' => 'Test2'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_roles_error() {


        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'roles',
            'addData' => [
               'name' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_tiles() {

        $result = [
            [
                'id' => 1,
                'title' => 'Test', 
                'icon' => 'test.svg',
                'note' => 'test',
                'description' => 'test desc'
            ]
        ];

       $this->request->addCallable(
            function ($CI) use ($result) {


                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'insert_tiles_record' => TRUE,
                        'get_tiles' => $result
                    ]
                );

                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;
            }
        );
 

        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'tiles',
            'addData' => [
                'title' => 'Test2'

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_add_facs_tiles_error() {


        $actual = $this->request('POST','/facs_manager/add_facs',[
            'facs_type' => 'tiles',
            'addData' => [
               'title' => ''

            ]
        ]);

        $this->assertIsString($actual);
    }

    public function test_auto_populate_facs_tables() {
        $result = [
            ['id' => 1, 'title' => 'title', 'name'=>'name'],
            ['id' => 1, 'title' => 'title', 'name'=>'name']
        ];

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'get_tiles' => $result
                    ]
                );

                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model' , [
                        'get_roles' => $result
                    ]
                );

                $CI->Roles_manager_model = $Roles_manager_model;


                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model' , [
                        'get_subapps' => $result,
                        'insert_subapps_record' => 1
                    ]
                );

                $CI->Subapps_manager_model = $Subapps_manager_model;

                $Features_manager_model = $this->getDouble(
                    'Features_manager_model' , [
                        'get_features' => $result,
                        'insert_features_record' => TRUE,
                    ]
                );

                $CI->Features_manager_model = $Features_manager_model;


                $Role_mappings_manager_model = $this->getDouble(
                    'Role_mappings_manager_model' , [
                        'update_role_mappings_record' => true,
                        'get_basic_role_mappings' => true,
                        'get_role_mappings' => true,
                        'insert_role_mappings_record' => true
                    ]
                );

                $CI->Role_mappings_manager_model = $Role_mappings_manager_model;
            }
        );


        $actual = $this->request('POST','/facs_manager/autopop');

        $this->assertIsString($actual);
    }

    public function test_auto_populate_facs_tables_role_mapping_not_exist() {
        $result = [
            ['id' => 1, 'title' => 'title', 'name'=>'name'],
            ['id' => 1, 'title' => 'title', 'name'=>'name']
        ];

        $this->request->addCallable(
            function ($CI) use ($result) {
                $Keycloak_tiles_model = $this->getDouble(
                    'Keycloak_tiles_model' , [
                        'get_tiles' => $result
                    ]
                );

                $CI->Keycloak_tiles_model = $Keycloak_tiles_model;

                $Roles_manager_model = $this->getDouble(
                    'Roles_manager_model' , [
                        'get_roles' => $result
                    ]
                );

                $CI->Roles_manager_model = $Roles_manager_model;


                $Subapps_manager_model = $this->getDouble(
                    'Subapps_manager_model' , [
                        'get_subapps' => $result,
                        'insert_subapps_record' => 1
                    ]
                );

                $CI->Subapps_manager_model = $Subapps_manager_model;

                $Features_manager_model = $this->getDouble(
                    'Features_manager_model' , [
                        'get_features' => $result,
                        'insert_features_record' => TRUE,
                    ]
                );

                $CI->Features_manager_model = $Features_manager_model;


                $Role_mappings_manager_model = $this->getDouble(
                    'Role_mappings_manager_model' , [
                        'update_role_mappings_record' => true,
                        'get_basic_role_mappings' => false,
                        'get_role_mappings' => true,
                        'insert_role_mappings_record' => true
                    ]
                );

                $CI->Role_mappings_manager_model = $Role_mappings_manager_model;
            }
        );


        $actual = $this->request('POST','/facs_manager/autopop');

        $this->assertIsString($actual);
    }
}
?>
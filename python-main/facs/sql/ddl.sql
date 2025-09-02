CREATE SCHEMA IF NOT EXISTS {{schema}};

CREATE TABLE IF NOT EXISTS {{schema}}.app (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.app_version (
  id BINARY(16) DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  app_id BINARY(16) NOT NULL,
  status ENUM ('active', 'deleted') NOT NULL DEFAULT ('active'),
  title VARCHAR(45),
  icon VARCHAR(45),
  note VARCHAR(45),
  description TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (app_id) REFERENCES {{schema}}.app(id),
  INDEX idx_created_app (app_id, created)
);

CREATE TABLE IF NOT EXISTS {{schema}}.subapp (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  app_id BINARY(16) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (app_id) REFERENCES {{schema}}.app(id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.subapp_version (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  subapp_id BINARY(16) NOT NULL,
  status ENUM ('active', 'deleted') NOT NULL DEFAULT ('active'),
  name TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (subapp_id) REFERENCES {{schema}}.subapp(id),
  INDEX idx_created_upa (subapp_id, created)
);

CREATE TABLE IF NOT EXISTS {{schema}}.feature (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  subapp_id BINARY(16) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (subapp_id) REFERENCES {{schema}}.subapp(id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.feature_version (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  feature_id BINARY(16) NOT NULL,
  status ENUM ('active', 'deleted') NOT NULL DEFAULT ('active'),
  name TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (feature_id) REFERENCES {{schema}}.feature(id),
  INDEX idx_created_upa (feature_id, created)
);

CREATE TABLE IF NOT EXISTS {{schema}}.upa_type (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  app_id BINARY(16),
  PRIMARY KEY (id),
  FOREIGN KEY (app_id) REFERENCES {{schema}}.app(id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.upa_type_version (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  upa_type_id BINARY(16) NOT NULL,
  status ENUM ('active', 'deleted') NOT NULL DEFAULT ('active'),
  name TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (upa_type_id) REFERENCES {{schema}}.upa_type(id),
  INDEX idx_created_upa (upa_type_id, created)
);

CREATE TABLE IF NOT EXISTS {{schema}}.user_role (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  upa_type_id BINARY(16) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (upa_type_id) REFERENCES {{schema}}.upa_type(id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.user_role_version (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  user_role_id BINARY(16) NOT NULL,
  status ENUM ('active', 'deleted') NOT NULL DEFAULT ('active'),
  name TEXT,
  PRIMARY KEY (id),
  FOREIGN KEY (user_role_id) REFERENCES {{schema}}.user_role(id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.role_feature_mapping (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  feature_id BINARY(16) NOT NULL,
  user_role_id BINARY(16) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (feature_id) REFERENCES {{schema}}.feature(id),
  FOREIGN KEY (user_role_id) REFERENCES {{schema}}.user_role(id)
);

CREATE TABLE IF NOT EXISTS {{schema}}.role_feature_mapping_version (
  id BINARY(16) NOT NULL DEFAULT (UUID_TO_BIN(UUID())),
  created TIMESTAMP NOT NULL DEFAULT NOW(),
  mapping_id BINARY(16) NOT NULL,
  status ENUM ('active', 'deleted') NOT NULL DEFAULT ('active'),
  PRIMARY KEY (id),
  FOREIGN KEY (mapping_id) REFERENCES {{schema}}.role_feature_mapping(id),
  INDEX idx_created_mapping (mapping_id, created)
);

CREATE TABLE IF NOT EXISTS {{schema}}.rfm_view (
  app_id BINARY(16) NOT NULL,
  subapp_id BINARY(16) NOT NULL,
  feature_id BINARY(16) NOT NULL,
  upa_type_id BINARY(16) NOT NULL,
  user_role_id BINARY(16) NOT NULL,
  mapping_id BINARY(16) NOT NULL,
  FOREIGN KEY (app_id) REFERENCES {{schema}}.app(id),
  FOREIGN KEY (subapp_id) REFERENCES {{schema}}.subapp(id),
  FOREIGN KEY (feature_id) REFERENCES {{schema}}.feature(id),
  FOREIGN KEY (upa_type_id) REFERENCES {{schema}}.upa_type(id),
  FOREIGN KEY (user_role_id) REFERENCES {{schema}}.user_role(id),
  FOREIGN KEY (mapping_id) REFERENCES {{schema}}.role_feature_mapping(id)
);

DELIMITER //
CREATE PROCEDURE {{schema}}.refresh_rfm_view()
BEGIN
  TRUNCATE TABLE {{schema}}.rfm_view;
  INSERT INTO {{schema}}.rfm_view
  SELECT
    t_subapp.app_id as app_id,
    t_feature.subapp_id as subapp_id,
    t_mapping.feature_id AS feature_id,
    t_role.upa_type_id AS upa_type_id,
    t_mapping.user_role_id AS user_role_id,
    t_mapping.mapping_id AS mapping_id
  FROM (
    SELECT * FROM
      {{schema}}.role_feature_mapping AS __table
    INNER JOIN (
      SELECT
	{{schema}}.role_feature_mapping_version.mapping_id
      FROM
	{{schema}}.role_feature_mapping_version
      INNER JOIN (
	SELECT
	  mapping_id,
	  max(created) as created
	FROM {{schema}}.role_feature_mapping_version
	GROUP BY mapping_id
	) AS __table_mapping_ver
      ON {{schema}}.role_feature_mapping_version.mapping_id = __table_mapping_ver.mapping_id
	AND {{schema}}.role_feature_mapping_version.created = __table_mapping_ver.created
      WHERE {{schema}}.role_feature_mapping_version.status='active'
    ) AS __table_mapping
    ON id = __table_mapping.mapping_id
  ) AS t_mapping
  INNER JOIN (
    SELECT * FROM
      {{schema}}.user_role AS __table
    INNER JOIN (
      SELECT
	{{schema}}.user_role_version.user_role_id
      FROM
	{{schema}}.user_role_version
      INNER JOIN (
	SELECT
	  user_role_id,
	  max(created) as created
	FROM {{schema}}.user_role_version
	GROUP BY user_role_id
	) AS __table_role_ver
      ON {{schema}}.user_role_version.user_role_id = __table_role_ver.user_role_id
	AND {{schema}}.user_role_version.created = __table_role_ver.created
      WHERE {{schema}}.user_role_version.status='active'
    ) AS __table_role
    ON id = __table_role.user_role_id
  ) AS t_role
  ON t_role.id = t_mapping.user_role_id
  INNER JOIN (
    SELECT * FROM
      {{schema}}.upa_type AS __table
    INNER JOIN (
      SELECT
	{{schema}}.upa_type_version.upa_type_id
      FROM
	{{schema}}.upa_type_version
      INNER JOIN (
	SELECT
	  upa_type_id,
	  max(created) as created
	FROM {{schema}}.upa_type_version
	GROUP BY upa_type_id
	) AS __table_upa_ver
      ON {{schema}}.upa_type_version.upa_type_id = __table_upa_ver.upa_type_id
	AND {{schema}}.upa_type_version.created = __table_upa_ver.created
      WHERE {{schema}}.upa_type_version.status='active'
    ) AS __table_upa
    ON id = __table_upa.upa_type_id
  ) AS t_upa
  ON t_upa.id = t_role.upa_type_id
  INNER JOIN (
    SELECT * FROM
      {{schema}}.feature AS __table
    INNER JOIN (
      SELECT
	{{schema}}.feature_version.feature_id
      FROM
	{{schema}}.feature_version
      INNER JOIN (
	SELECT
	  feature_id,
	  max(created) as created
	FROM {{schema}}.feature_version
	GROUP BY feature_id
	) AS __table_feature_ver
      ON {{schema}}.feature_version.feature_id = __table_feature_ver.feature_id
	AND {{schema}}.feature_version.created = __table_feature_ver.created
      WHERE {{schema}}.feature_version.status='active'
    ) AS __table_feature
    ON id = __table_feature.feature_id
  ) AS t_feature
  ON t_feature.id = t_mapping.feature_id
  INNER JOIN (
    SELECT * FROM
      {{schema}}.subapp AS __table
    INNER JOIN (
      SELECT
	{{schema}}.subapp_version.subapp_id
      FROM
	{{schema}}.subapp_version
      INNER JOIN (
	SELECT
	  subapp_id,
	  max(created) as created
	FROM {{schema}}.subapp_version
	GROUP BY subapp_id
	) AS __table_subapp_ver
      ON {{schema}}.subapp_version.subapp_id = __table_subapp_ver.subapp_id
	AND {{schema}}.subapp_version.created = __table_subapp_ver.created
      WHERE {{schema}}.subapp_version.status='active'
    ) AS __table_subapp
    ON id = __table_subapp.subapp_id
  ) AS t_subapp
  ON t_subapp.id = t_feature.subapp_id
  WHERE t_subapp.app_id = t_upa.app_id;

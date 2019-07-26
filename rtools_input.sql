CREATE TABLE IF NOT EXISTS rtools_inputs (
  user_id int(5) unsigned NOT NULL,
  page_id int(10) unsigned NOT NULL,
  code_name varchar(255) NOT NULL,
  input_name varchar(255) NOT NULL,
  input_value varchar(255) NOT NULL,
  timestamp timestamp NULL DEFAULT NULL,
  PRIMARY KEY (user_id,page_id,code_name,input_name)
)
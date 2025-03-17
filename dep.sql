CREATE TABLE client (
  user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  surname VARCHAR(100) NOT NULL,
  login VARCHAR(100) NOT NULL,
  password VARCHAR(50) NOT NULL,
  phone_num VARCHAR(14) NOT NULL,
  email VARCHAR(100) NOT NULL,
  passport CHAR(10) NOT NULL
);

CREATE TABLE worker (
  user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  surname VARCHAR(100) NOT NULL,
  login VARCHAR(100) NOT NULL,
  password VARCHAR(50) NOT NULL,
  phone_num VARCHAR(14) NOT NULL,
  email VARCHAR(100) NOT NULL,
  role CHAR(30) NOT NULL
);


CREATE TABLE deposit (
  deposit_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  currency CHAR(3) NOT NULL,
  --0 for formula without capitalization, 1 for formula with it  
  formula INTEGER NOT NULL,
  per_cent FLOAT NOT NULL,
  -- duration count in months
  duration INTEGER NOT NULL,
  min_start_amount INTEGER, CONSTRAIN c CHECK (min_start_amount >= 0),
  fee FLOAT,
  begin_date DATE NOT NULL,
  end_date DATE NOT NULL
);

CREATE TABLE client_deposit (
  client_deposit_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  client_id INTEGER NOT NULL,
  deposit_id INTEGER NOT NULL,
  opening_date DATE NOT NULL,
  money_amount INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (client_id) REFERENCES client (client_id),
  FOREIGN KEY (deposit_id) REFERENCES deposit (deposit_id)
);


CREATE TABLE operation (
  operation_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  client_deposit_id INTEGER NOT NULL,
  operation_type CHAR(1) NOT NULL,
  amount INTEGER NOT NULL, CONSTRAIN c1 CHECK (amount >= 0),
  FOREIGN KEY (client_deposit_id) REFERENCES client_deposit (client_deposit_id)
);

CREATE TABLE place (
  place_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100)
);

CREATE TABLE deposit_in_work (
  deposit_in_work_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  worker_id INTEGER NOT NULL,
  client_deposit_id INTEGER NOT NULL,
  place_id INTEGER NOT NULL
  FOREIGN KEY (client_deposit_id) REFERENCES client_deposit (client_deposit_id),
  FOREIGN KEY (worker_id) REFERENCES worker (worker_id),
  FOREIGN KEY (place_id) REFERENCES place (place_id)
);

CREATE TABLE question (
  question_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  theme VARCHAR(200),
  q_message TEXT NOT NULL,
  client_id INTEGER NOT NULL,
  status INTEGER NOT NULL DEFAULT 0,
  FOREIGN KEY (client_id) REFERENCES client (client_id)
);


CREATE TABLE answer (
  answer_id INTEGER PRIMARY KEY AUTO_INCREMENT,
  question_id INTEGER NOt NULL,
  worker_id INTEGER NOT NULL,
  a_message TEXT NOT NULL,
  FOREIGN KEY (worker_id) REFERENCES worker (worker_id)
  FOREIGN KEY (question_id) REFERENCES question (question_id)
);
 
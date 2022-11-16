
DROP DATABASE if exists HTML_FILES ;
CREATE DATABASE HTML_FILES;
USE HTML_FILES;

create user PhpAdmin identified by 'HTML_FILES';
grant all privileges on HTML_FILES.* to PhpAdmin;


drop table if exists words;
CREATE TABLE words (
  id_word INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  word varchar(100),
  word_order varchar(100) 
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

drop table if exists documents;
CREATE TABLE documents (
  id_document INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title TEXT NULL,
  path TEXT NULL,
  description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

drop table if exists join_documents_and_words;
CREATE TABLE join_documents_and_words (
  id_word INT NOT NULL,
  id_document INT NOT NULL,
  PRIMARY KEY (id_word, id_document),
  weight FLOAT NULL,
  CONSTRAINT weight_document
    FOREIGN KEY (id_document)
    REFERENCES HTML_FILES.documents (id_document)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT weight_word
    FOREIGN KEY (id_word)
    REFERENCES HTML_FILES.words (id_word)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


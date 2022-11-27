/*exécuter ces commandes sur votre terminal*/
/*1- $ sudo mysql */
/*2- $ source /le chemin ou ce trouve ce fichier/identifiat.sql*/

USE HTML_FILES;

grant all privileges on HTML_FILES.* to PhpAdmin;

drop table if exists Login_Admin;
CREATE TABLE Login_Admin (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username varchar(100) NOT NULL,
  email varchar(100) NOT NULL,
  Password varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*le mots de passe c'est Tutu75 */		
INSERT INTO Login_Admin values( 1 ,'Admin2022','admin2022@gmail.com','c4a0b7848bf1526e502f68b2c296f384d1aeee3857780b90ce2ddf7530875a27');
SELECT * from Login_Admin ; 


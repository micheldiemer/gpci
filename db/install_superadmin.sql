
-- création d'un nouvel utilisateur superadmin
delete from users where login = 'superadmin';
insert into users (login, password, firstName, lastName,
                   email, token, enabled, connected)
values ('superadmin','','admin','admin',
        'superadmin@example.org','',1,0);

--  mise à jour du hash et du mot de passe
--  ici le mot de passe est adminXxx123#
update users
   set hash = 'EDAGZRYZERDX'
       , password =  sha1(concat('EDAGZRYZERDX',sha1('adminXxx123#')))
 where login = 'superadmin';

--  récupération de l'id du superadmin
select id from users where login = 'superadmin';

--  attributions des rôles
--  administrateur, planificateur, enseignant
--  cf. select * from roles
insert into users_roles values(1,(select id from users where login = 'superadmin'));
insert into users_roles values(2,(select id from users where login = 'superadmin'));
insert into users_roles values(3,(select id from users where login = 'superadmin'));

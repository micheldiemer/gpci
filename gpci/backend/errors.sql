
-- Cours sans classes
 select * from cours where id_Cours not in (select id_Cours from cours_classes);

-- Cours sans formateur
select cours.id, cours.start, cours.end, matieres.nom from cours join matieres on id_Matieres = matieres.id where id_U
sers is null order by start;

-- Cours sans formateur
select cours.id, cours.start, cours.end, matieres.nom, classes.nom
  from cours 
       join matieres on id_Matieres = matieres.id 
       join cours_classes on id_Cours = cours.id
       join classes on id_Classes = classes.id
 where cours.id_Users is null order by start;

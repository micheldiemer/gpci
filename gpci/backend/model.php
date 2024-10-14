<?php

/**
 * \file        model.php
 * \author      SIO-SLAM 2014-2016
 * \version     1.1
 * \date        11/19/2015
 * \brief       model for classes
 *
 * \details     this file contains all the models built with slim framework
 */

// Connexion à la BDD
$container = new \Illuminate\Container\Container;
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
$conn = $connFactory->make($settings);
$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');


$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings);
$capsule->setAsGlobal();
$capsule->bootEloquent();

\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);

use \Illuminate\Database\Eloquent\Model;

/**
 * \class        Users model.php "backend/model.php
 * \brief        corresponding to the registered users
 */
class Users extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = array('login', 'password', 'firstName', 'firstName', 'lastName', 'email', 'token', 'enabled', 'connected', 'hash', 'theme', 'tokenCDate');

    public function roles()
    {
        return $this->belongsToMany(Roles::class, 'users_roles', 'id_Users', 'id_Roles');
    }

    public function matieres()
    {
        return $this->belongsToMany(Matieres::class, 'users_matieres', 'id_Users', 'id_Matieres');
    }

    public function indisponibilite()
    {
        return $this->hasMany(Indisponibilite::class, 'id_Users');
    }

    public function cours()
    {
        return $this->hasMany(Cours::class, 'id_Users');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'id_Users');
    }
}

/**
 * \class        Classes model.php "backend/model.php
 * \brief        corresponding to the classes
 * \details		corresponding to the "classes". A "classe" is composed by many students
 */
class Classes extends Model
{
    public $timestamps = false;
    public $fillable = array('nom', 'start', 'end', 'id_Users');
    public $nbcours = 0;
    public $listeCours = [];

    public function user()
    {
        return $this->belongsTo(Users::class, 'id_Users')->select("firstName", "lastName", "id");
    }

    public function cours()
    {
        return $this->belongsToMany(Cours::class, 'cours_classes', 'id_Classes', 'id_Cours');
    }

    public function nbCours()
    {
        $this->nbcours = $this->cours()->whereRaw('cours.start >= \'' . $this->start . '\' and cours.end <= \'' . $this->end . '\'')->count();
        return $this->nbcours;
    }
}

/**
 * \class        Fermeture model.php "backend/model.php
 * \brief        corresponding to the day the school is close
 */
class Fermeture extends Model
{
    public $timestamps = false;
}

/**
 * \class        Indisponibilite model.php "backend/model.php
 * \brief        corresponding to the unusable hours in the teacher's schedule
 */
class Indisponibilite extends Model
{
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(Users::class, 'id_Users');
    }
}

/**
 * \class        Matieres model.php "backend/model.php
 * \brief        corresponding to the lesson's subject f.e. : mathematics, english
 */
class Matieres extends Model
{
    public $timestamps = false;

    protected $fillable = array('nom', 'code');

    public function user()
    {
        return $this->belongsToMany(Users::class, 'users_matieres', 'id_Matieres', 'id_Users');
    }
    public function cours()
    {
        return $this->hasMany(Cours::class, 'id_Matieres');
    }
}

/**
 * \class        Cours Cours.php "backend/model.php
 * \brief        corresponding to lessons
 */
class Cours extends Model
{
    public $timestamps = false;
    public $fillable = array('start', 'end', 'id_Matieres', 'id_Salles');
    public function user()
    {
        return $this->belongsTo(Users::class, 'id_Users')->with('matieres')->select('id', 'firstName', 'lastName', 'email');
    }

    public function matiere()
    {
        return $this->belongsTo(Matieres::class, 'id_Matieres');
    }

    public function salle()
    {
        return $this->belongsTo(Salles::class, 'id_Salles');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'cours_classes', 'id_Cours', 'id_Classes')->select('id', 'nom');
    }
}

/**
 * \class        Roles model.php "backend/model.php
 * \brief        Keeping the different roles an user can have
 * \details		corresponding to the role an user has. He can be : Administrateur,Planificateur or Enseignant
 */
class Roles extends Model
{
    public $timestamps = false;
    protected $casts = [
        'priority' => 'int',
    ];
    public function user()
    {
        return $this->belongsToMany(Users::class, 'users_matieres', 'id_Roles', 'id_Users');
    }
}

class Salles extends Model
{
    public $timestamps = false;
    protected $fillable = array('nom');

    public function cours()
    {
        return $this->hasMany(Cours::class, 'id_Salles');
    }
}


function DB_get_classes_periods($date) {
  global $capsule;
  return $capsule::table('classes')->whereRaw('(start >= ? and start <= ?) or (end >= ? and end <= ? )', [$date['start'], $date['end'], $date['start'], $date['end']])->selectRaw('MIN(start) as start, MAX(end) as end')->get()->toArray();

}

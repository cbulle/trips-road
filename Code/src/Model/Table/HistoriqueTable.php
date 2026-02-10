<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class HistoriqueTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Si ta table s'appelle exactement 'historique' (singulier) en base de données
        $this->setTable('historique'); 
        $this->setPrimaryKey('id');

        // La relation : Un historique appartient à un Roadtrip
        $this->belongsTo('Roadtrips', [
            'foreignKey' => 'roadtrip_id',
            'joinType' => 'INNER' // Ignore les historiques de roadtrips supprimés
        ]);

        // La relation : Un historique appartient à l'utilisateur qui regarde
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
    }
}
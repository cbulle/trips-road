<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Roadtrips Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\FavoritesTable&\Cake\ORM\Association\HasMany $Favorites
 * @property \App\Model\Table\HistoriesTable&\Cake\ORM\Association\HasMany $Histories
 * @property \App\Model\Table\SharedRoadtripsTable&\Cake\ORM\Association\HasMany $SharedRoadtrips
 * @property \App\Model\Table\TripsTable&\Cake\ORM\Association\HasMany $Trips
 * @property \App\Model\Table\PointsOfInterestsTable&\Cake\ORM\Association\BelongsToMany $PointsOfInterests
 *
 * @method \App\Model\Entity\Roadtrip newEmptyEntity()
 * @method \App\Model\Entity\Roadtrip newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Roadtrip> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Roadtrip get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Roadtrip findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Roadtrip patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Roadtrip> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Roadtrip|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Roadtrip saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Roadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Roadtrip>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Roadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Roadtrip> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Roadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Roadtrip>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Roadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Roadtrip> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RoadtripsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('roadtrips');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'roadtrip_id',
        ]);
        $this->hasMany('Favorites', [
            'foreignKey' => 'roadtrip_id',
        ]);
        $this->hasMany('Histories', [
            'foreignKey' => 'roadtrip_id',
        ]);
        $this->hasMany('SharedRoadtrips', [
            'foreignKey' => 'roadtrip_id',
        ]);
        $this->hasMany('Trips', [
            'foreignKey' => 'roadtrip_id',
        ]);
        $this->belongsToMany('PointsOfInterests', [
            'foreignKey' => 'roadtrip_id',
            'targetForeignKey' => 'points_of_interest_id',
            'joinTable' => 'points_of_interests_roadtrips',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('visibility')
            ->allowEmptyString('visibility');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('photo_url')
            ->maxLength('photo_url', 255)
            ->allowEmptyString('photo_url');

        $validator
            ->scalar('status')
            ->allowEmptyString('status');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * PointsOfInterests Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\CommentsTable&\Cake\ORM\Association\HasMany $Comments
 * @property \App\Model\Table\RoadtripsTable&\Cake\ORM\Association\BelongsToMany $Roadtrips
 *
 * @method \App\Model\Entity\PointsOfInterest newEmptyEntity()
 * @method \App\Model\Entity\PointsOfInterest newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\PointsOfInterest> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\PointsOfInterest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\PointsOfInterest findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\PointsOfInterest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\PointsOfInterest> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\PointsOfInterest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\PointsOfInterest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\PointsOfInterest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PointsOfInterest>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PointsOfInterest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PointsOfInterest> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PointsOfInterest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PointsOfInterest>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\PointsOfInterest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\PointsOfInterest> deleteManyOrFail(iterable $entities, array $options = [])
 */
class PointsOfInterestsTable extends Table
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

        $this->setTable('points_of_interests');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'points_of_interest_id',
        ]);
        $this->belongsToMany('Roadtrips', [
            'foreignKey' => 'points_of_interest_id',
            'targetForeignKey' => 'roadtrip_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->decimal('latitude')
            ->allowEmptyString('latitude');

        $validator
            ->decimal('longitude')
            ->allowEmptyString('longitude');

        $validator
            ->scalar('category')
            ->maxLength('category', 100)
            ->allowEmptyString('category');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

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

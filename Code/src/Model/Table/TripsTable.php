<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Trips Model
 *
 * @property \App\Model\Table\RoadtripsTable&\Cake\ORM\Association\BelongsTo $Roadtrips
 * @property \App\Model\Table\SubStepsTable&\Cake\ORM\Association\HasMany $SubSteps
 *
 * @method \App\Model\Entity\Trip newEmptyEntity()
 * @method \App\Model\Entity\Trip newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Trip> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Trip get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Trip findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Trip patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Trip> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Trip|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Trip saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Trip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Trip>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Trip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Trip> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Trip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Trip>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Trip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Trip> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TripsTable extends Table
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

        $this->setTable('trips');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roadtrips', [
            'foreignKey' => 'roadtrip_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('SubSteps', [
            'foreignKey' => 'trip_id',
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
            ->integer('order_number')
            ->requirePresence('order_number', 'create')
            ->notEmptyString('order_number');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('departure')
            ->maxLength('departure', 255)
            ->requirePresence('departure', 'create')
            ->notEmptyString('departure');

        $validator
            ->scalar('arrival')
            ->maxLength('arrival', 255)
            ->requirePresence('arrival', 'create')
            ->notEmptyString('arrival');

        $validator
            ->scalar('transport_mode')
            ->maxLength('transport_mode', 50)
            ->allowEmptyString('transport_mode');

        $validator
            ->integer('roadtrip_id')
            ->notEmptyString('roadtrip_id');

        $validator
            ->boolean('avoid_highways')
            ->allowEmptyString('avoid_highways');

        $validator
            ->boolean('avoid_tolls')
            ->allowEmptyString('avoid_tolls');

        $validator
            ->time('departure_time')
            ->notEmptyTime('departure_time');

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
        $rules->add($rules->existsIn(['roadtrip_id'], 'Roadtrips'), ['errorField' => 'roadtrip_id']);

        return $rules;
    }
}

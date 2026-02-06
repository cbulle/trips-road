<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SubSteps Model
 *
 * @property \App\Model\Table\TripsTable&\Cake\ORM\Association\BelongsTo $Trips
 * @property \App\Model\Table\SubStepPhotosTable&\Cake\ORM\Association\HasMany $SubStepPhotos
 *
 * @method \App\Model\Entity\SubStep newEmptyEntity()
 * @method \App\Model\Entity\SubStep newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SubStep> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SubStep get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SubStep findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SubStep patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SubStep> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SubStep|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SubStep saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SubStep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStep>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SubStep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStep> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SubStep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStep>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SubStep>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStep> deleteManyOrFail(iterable $entities, array $options = [])
 */
class SubStepsTable extends Table
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

        $this->setTable('sub_steps');
        $this->setDisplayField('city');
        $this->setPrimaryKey('id');

        $this->belongsTo('Trips', [
            'foreignKey' => 'trip_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('SubStepPhotos', [
            'foreignKey' => 'sub_step_id',
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
            ->scalar('city')
            ->maxLength('city', 255)
            ->requirePresence('city', 'create')
            ->notEmptyString('city');

        $validator
            ->scalar('description')
            ->maxLength('description', 4294967295)
            ->allowEmptyString('description');

        $validator
            ->integer('trip_id')
            ->notEmptyString('trip_id');

        $validator
            ->scalar('transport_type')
            ->maxLength('transport_type', 50)
            ->requirePresence('transport_type', 'create')
            ->notEmptyString('transport_type');

        $validator
            ->time('heure')
            ->allowEmptyTime('heure');

        $validator
            ->boolean('avoid_highways')
            ->allowEmptyString('avoid_highways');

        $validator
            ->boolean('avoid_tolls')
            ->allowEmptyString('avoid_tolls');

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
        $rules->add($rules->existsIn(['trip_id'], 'Trips'), ['errorField' => 'trip_id']);

        return $rules;
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SharedRoadtrips Model
 *
 * @property \App\Model\Table\RoadtripsTable&\Cake\ORM\Association\BelongsTo $Roadtrips
 *
 * @method \App\Model\Entity\SharedRoadtrip newEmptyEntity()
 * @method \App\Model\Entity\SharedRoadtrip newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SharedRoadtrip> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SharedRoadtrip get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SharedRoadtrip findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SharedRoadtrip patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SharedRoadtrip> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SharedRoadtrip|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SharedRoadtrip saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SharedRoadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SharedRoadtrip>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SharedRoadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SharedRoadtrip> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SharedRoadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SharedRoadtrip>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SharedRoadtrip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SharedRoadtrip> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SharedRoadtripsTable extends Table
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

        $this->setTable('shared_roadtrips');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roadtrips', [
            'foreignKey' => 'roadtrip_id',
            'joinType' => 'INNER',
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
            ->integer('roadtrip_id')
            ->notEmptyString('roadtrip_id');

        $validator
            ->scalar('token')
            ->maxLength('token', 255)
            ->requirePresence('token', 'create')
            ->notEmptyString('token');

        $validator
            ->integer('view_count')
            ->notEmptyString('view_count');

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

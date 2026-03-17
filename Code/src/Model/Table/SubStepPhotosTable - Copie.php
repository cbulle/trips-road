<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SubStepPhotos Model
 *
 * @property \App\Model\Table\SubStepsTable&\Cake\ORM\Association\BelongsTo $SubSteps
 *
 * @method \App\Model\Entity\SubStepPhoto newEmptyEntity()
 * @method \App\Model\Entity\SubStepPhoto newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\SubStepPhoto> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SubStepPhoto get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\SubStepPhoto findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\SubStepPhoto patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\SubStepPhoto> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SubStepPhoto|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\SubStepPhoto saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\SubStepPhoto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStepPhoto>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SubStepPhoto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStepPhoto> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SubStepPhoto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStepPhoto>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\SubStepPhoto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\SubStepPhoto> deleteManyOrFail(iterable $entities, array $options = [])
 */
class SubStepPhotosTable extends Table
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

        $this->setTable('sub_step_photos');
        $this->setDisplayField('photo');
        $this->setPrimaryKey('id');

        $this->belongsTo('SubSteps', [
            'foreignKey' => 'sub_step_id',
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
            ->integer('sub_step_id')
            ->notEmptyString('sub_step_id');

        $validator
            ->scalar('photo')
            ->maxLength('photo', 255)
            ->requirePresence('photo', 'create')
            ->notEmptyString('photo');

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
        $rules->add($rules->existsIn(['sub_step_id'], 'SubSteps'), ['errorField' => 'sub_step_id']);

        return $rules;
    }
}

<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "apple".
 *
 * @property int $id
 * @property string $color
 * @property int $appeared_at
 * @property int|null $fell_at
 * @property int $status
 * @property int $eaten_percent
 * @property float $size
 * @property int|null $rotten_at
 * @property int|null $eaten_at
 * @property int $created_at
 * @property int $updated_at
 */
class Apple extends \yii\db\ActiveRecord
{
    public const STATUS_ON_TREE = 0;
    public const STATUS_ON_GROUND = 1;
    public const STATUS_ROTTEN = 2;

    // Store color NAME in DB; HEX is used only for UI
    public const COLOR_MAP = [
        'green' => '#7fbf3f',
        'red' => '#a83232',
        'yellow' => '#f2c94c',
        'red_orange' => '#e07a5f',
        'light_green' => '#8bbf3f',
        'dark_red' => '#c0392b',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'apple';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['fell_at', 'rotten_at', 'eaten_at'], 'default', 'value' => null],
            [['color'], 'default', 'value' => function () { return self::randomColor(); }],
            [['appeared_at'], 'default', 'value' => function () { return self::randomAppearedAt(); }],
            [['status'], 'default', 'value' => self::STATUS_ON_TREE],
            [['eaten_percent'], 'default', 'value' => 0],
            [['size'], 'default', 'value' => 1.00],
            [['created_at', 'updated_at'], 'default', 'value' => function () { return time(); }],
            [['color', 'appeared_at', 'created_at', 'updated_at'], 'required'],
            [['appeared_at', 'fell_at', 'status', 'eaten_percent', 'rotten_at', 'eaten_at', 'created_at', 'updated_at'], 'integer'],
            [['size'], 'number'],
            [['color'], 'string', 'max' => 32],
            ['status', 'in', 'range' => [self::STATUS_ON_TREE, self::STATUS_ON_GROUND, self::STATUS_ROTTEN]],
            ['color', 'in', 'range' => array_keys(self::COLOR_MAP)],
            ['eaten_percent', 'integer', 'min' => 0, 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'color' => 'Color',
            'appeared_at' => 'Appeared At',
            'fell_at' => 'Fell At',
            'status' => 'Status',
            'eaten_percent' => 'Eaten Percent',
            'size' => 'Size',
            'rotten_at' => 'Rotten At',
            'eaten_at' => 'Eaten At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ON_TREE => 'Висит на дереве',
            self::STATUS_ON_GROUND => 'Лежит на земле',
            self::STATUS_ROTTEN => 'Гнилое яблоко',
        ];
    }

    public function getStatusLabel(): string
    {
        $labels = self::statusLabels();
        return $labels[$this->status] ?? 'Неизвестно';
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $now = time();

        if ($insert) {
            if (empty($this->color)) {
                $this->color = self::randomColor();
            }
            if (empty($this->appeared_at)) {
                $this->appeared_at = self::randomAppearedAt();
            }
            if ($this->status === null) {
                $this->status = self::STATUS_ON_TREE;
            }
            if ($this->eaten_percent === null) {
                $this->eaten_percent = 0;
            }
            if ($this->size === null) {
                $this->size = 1.00;
            }
            if (empty($this->created_at)) {
                $this->created_at = $now;
            }
        }

        $this->updated_at = $now;

        return true;
    }

    public function isOnTree(): bool
    {
        return (int)$this->status === self::STATUS_ON_TREE;
    }

    public function isOnGround(): bool
    {
        return (int)$this->status === self::STATUS_ON_GROUND;
    }

    public function isRotten(): bool
    {
        // При любом обращении к состоянию гнилости
        // сначала актуализируем его и сохраняем в БД при необходимости
        $this->updateRottenState();

        return (int)$this->status === self::STATUS_ROTTEN;
    }

    /**
     * Обновить состояние гнилости: если яблоко лежит на земле более 5 часов, оно портится.
     */
    public function updateRottenState(): void
    {
        if ((int)$this->status === self::STATUS_ROTTEN || !$this->fell_at) {
            return;
        }

        $fiveHours = 1 * 60 * 60;
        if (time() - (int)$this->fell_at >= $fiveHours) {
            $this->status = self::STATUS_ROTTEN;
            $this->rotten_at = time();
            // Фиксируем изменение в базе без валидации,
            // т.к. бизнес-правило уже выполнено
            $this->save(false, ['status', 'rotten_at', 'updated_at']);
        }
    }

    /**
     * Упасть с дерева на землю.
     *
     * @throws \DomainException
     */
    public function fallToGround(): void
    {
        if (!$this->isOnTree()) {
            throw new \DomainException('Яблоко уже не на дереве.');
        }

        $this->fell_at = time();
        $this->status = self::STATUS_ON_GROUND;
        $this->save(false);
    }

    /**
     * Съесть часть яблока.
     *
     * @param int $percent Процент, который нужно съесть (1-100)
     * @return bool true, если яблоко полностью съедено и удалено
     * @throws \DomainException
     */
    public function eat(int $percent): bool
    {
        if ($percent <= 0) {
            throw new \DomainException('Процент должен быть больше нуля.');
        }

        $this->updateRottenState();

        if ($this->isOnTree()) {
            throw new \DomainException('Съесть нельзя, яблоко на дереве.');
        }

        if ($this->isRotten()) {
            throw new \DomainException('Съесть нельзя, яблоко испорчено.');
        }

        if ((int)$this->eaten_percent >= 100) {
            throw new \DomainException('Яблоко уже полностью съедено.');
        }

        $newPercent = min(100, (int)$this->eaten_percent + $percent);
        $this->eaten_percent = $newPercent;
        $this->size = max(0, 1 - $this->eaten_percent / 100);

        if ($newPercent >= 100) {
            $this->eaten_at = time();
            // Удаляем запись, так как яблоко полностью съедено
            $this->delete();
            return true;
        }

        $this->save(false);
        return false;
    }

    public static function randomColor(): string
    {
        $names = array_keys(self::COLOR_MAP);
        return $names[array_rand($names)];
    }

    public static function randomAppearedAt(): int
    {
        $now = time();
        $weekAgo = $now - 7 * 24 * 60 * 60;
        return mt_rand($weekAgo, $now);
    }

    public function getColorHex(): ?string
    {
        return self::COLOR_MAP[$this->color] ?? null;
    }

}


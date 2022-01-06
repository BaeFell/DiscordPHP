<?php

/*
 * This file is a part of the DiscordPHP project.
 *
 * Copyright (c) 2021 David Cole <david.cole1340@gmail.com>
 *
 * This source file is subject to the MIT license which is
 * bundled with this source code in the LICENSE.md file.
 */

namespace Discord\Parts\Interactions\Command;

use Discord\Helpers\Collection;
use Discord\Parts\Part;

use function Discord\poly_strlen;

/**
 * Option represents an array of options that can be given to a command.
 * 
 * @link https://discord.com/developers/docs/interactions/application-commands#application-command-object-application-command-option-structure
 *
 * @property int                      $type          Type of the option.
 * @property string                   $name          Name of the option.
 * @property string                   $description   1-100 character description.
 * @property bool                     $required      If the parameter is required or optional--default false.
 * @property Collection|Choice[]|null $choices       Choices for STRING, INTEGER, and NUMBER types for the user to pick from, max 25.
 * @property Collection|Option[]      $options       Sub-options if applicable.
 * @property array                    $channel_types If the option is a channel type, the channels shown will be restricted to these types.
 * @property int|float                $min_value     If the option is an INTEGER or NUMBER type, the minimum value permitted.
 * @property int|float                $max_value     If the option is an INTEGER or NUMBER type, the maximum value permitted.
 * @property bool                     $autocomplete  Enable autocomplete interactions for this option.
 */
class Option extends Part
{
    public const SUB_COMMAND = 1;
    public const SUB_COMMAND_GROUP = 2;
    public const STRING = 3;
    public const INTEGER = 4; // Any integer between -2^53 and 2^53
    public const BOOLEAN = 5;
    public const USER = 6;
    public const CHANNEL = 7; // Includes all channel types + categories
    public const ROLE = 8;
    public const MENTIONABLE = 9; // Includes users and roles
    public const NUMBER = 10; // Any double between -2^53 and 2^53

    /**
     * @inheritdoc
     */
    protected $fillable = ['type', 'name', 'description', 'required', 'choices', 'options', 'channel_types', 'min_value', 'max_value', 'autocomplete'];

    /**
     * Gets the choices attribute.
     *
     * @return Collection|Choices[]|null A collection of choices.
     */
    protected function getChoicesAttribute(): ?Collection
    {
        if (! isset($this->attributes['choices'])) {
            return null;
        }

        $choices = Collection::for(Choice::class, null);

        foreach ($this->attributes['choices'] ?? [] as $choice) {
            $choices->push($this->factory->create(Choice::class, $choice, true));
        }

        return $choices;
    }

    /**
     * Gets the options attribute.
     *
     * @return Collection|Options[] A collection of options.
     */
    protected function getOptionsAttribute(): Collection
    {
        $options = Collection::for(Option::class, null);

        foreach ($this->attributes['options'] ?? [] as $option) {
            $options->push($this->factory->create(Option::class, $option, true));
        }

        return $options;
    }

    /**
     * Sets the type of the option.
     *
     * @param int $type type of the option
     *
     * @return $this
     */
    public function setType(int $type)
    {
        if ($type < 1 || $type > 10) {
            throw new \InvalidArgumentException('Invalid type provided.');
        }

        $this->type = $type;
        return $this;
    }

    /**
     * Sets the name of the option.
     *
     * @param string $name name of the option
     *
     * @return $this
     */
    public function setName(string $name)
    {
        if ($name && poly_strlen($name) > 32) {
            throw new \InvalidArgumentException('Name must be less than or equal to 32 characters.');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * Sets the description of the option.
     *
     * @param string $description description of the option
     *
     * @return $this
     */
    public function setDescription(string $description)
    {
        if ($description && poly_strlen($description) > 100) {
            throw new \InvalidArgumentException('Description must be less than or equal to 100 characters.');
        }

        $this->description = $description;
        return $this;
    }

    /**
     * Sets the requirement of the option.
     *
     * @param bool $required requirement of the option
     *
     * @return $this
     */
    public function setRequired(bool $required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Sets the channel types of the option.
     *
     * @param array $types types of the channel
     *
     * @return $this
     */
    public function setChannelTypes(array $types)
    {
        $this->channel_types = $types;
        return $this;
    }

    /**
     * Adds an option to the option.
     *
     * @param Option $option The option
     *
     * @return $this
     */
    public function addOption(Option $option)
    {
        if (count($this->options) >= 25) {
            throw new \RangeException('Option can not have more than 25 parameters.');
        }

        $this->attributes['options'][] = $option->getRawAttributes();
        return $this;
    }

    /**
     * Adds a choice to the option.
     *
     * @param Choice $choice The choice
     *
     * @return $this
     */
    public function addChoice(Choice $choice)
    {
        if (count($this->choices) >= 25) {
            throw new \RangeException('Option can only have a maximum of 25 Choices.');
        }

        $this->attributes['choices'][] = $choice->getRawAttributes();
        return $this;
    }

    /**
     * Removes an option.
     *
     * @param Option $option Option to remove.
     *
     * @return $this
     */
    public function removeOption(Option $option)
    {
        if ($opt = $this->attributes['options']->offsetGet($option->name)) {
            $this->attributes['options']->offsetUnset($opt);
        }

        return $this;
    }

    /**
     * Removes a choice.
     *
     * @param Choice $choice Choice to remove
     *
     * @return $this
     */
    public function removeChoice(Choice $choice)
    {
        if ($cho = $this->attributes['choices']->offsetGet($choice->name)) {
            $this->attributes['choices']->offsetUnset($cho);
        }

        return $this;
    }

    /**
     * Sets the minimum value permitted.
     *
     * @param int|float $min_value integer for INTEGER options, double for NUMBER options
     *
     * @return $this
     */
    public function setMinValue($min_value)
    {
        $this->min_value = $min_value;
        return $this;
    }

    /**
     * Sets the minimum value permitted.
     *
     * @param int|float $min_value integer for INTEGER options, double for NUMBER options
     *
     * @return $this
     */
    public function setMaxValue($max_value)
    {
        $this->max_value = $max_value;
        return $this;
    }

    /**
     * Sets the autocomplete interactions for this option.
     *
     * @param bool $autocomplete enable autocomplete interactions for this option
     *
     * @return $this
     */
    public function setAutoComplete(bool $autocomplete)
    {
        if ($autocomplete && !empty($this->attributes['choices'])) {
            throw new \InvalidArgumentException('Autocomplete may not be set to true if choices are present.');
        }

        $this->autocomplete = $autocomplete;
        return $this;
    }
}
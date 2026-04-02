<?php

namespace slowly\final_cli;

use BackedEnum;
use ReflectionEnum;
use UnitEnum;

enum enum_type {

    case unit;
    case backed;

    static public function is_enum(?string $class = null): false|self {
        if ($class === null) return false;
        if (self::is_backed($class)) return self::backed;
        if (self::is_unit($class)) return self::unit;
        return false;
    }

    static public function is_backed(string $type): bool {
        return is_subclass_of($type, BackedEnum::class);
    }

    static public function is_unit(string $type): bool {
        return is_subclass_of($type, UnitEnum::class);
    }

    public function from_input_string(string $type, string $input): BackedEnum|UnitEnum {
        return match ($this) {
            self::unit => new ReflectionEnum($type)->getCase($input),
            self::backed => $type::from($input)
        };
    }

    public function cases_as_strings(string $class): array {
        return array_map(
            fn(BackedEnum|UnitEnum $case) => $this == self::backed ? $case->value : $case->name,
            $class::cases(),
        );
    }
}

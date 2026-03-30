## rules

    // required argument
    string $_input_file => <input-file>

    // optional argument
    ?string $_input_file=null => [input-file]

    // optional flag
    bool $force => --force

    // option with required value
    string $in => --in <in>

    // option with short alias with required value
    #[alias("i")]
    string $in => -i|--in <in>

    // option with required value and documented name
    string $in__input_file => --in <input-file>

    // option with optional value and documented name
    string $in__input_file="/dev/null" => --in [input-file] default: /dev/null

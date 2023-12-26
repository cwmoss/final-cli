## rules

    definition                  optional default help
    bool force                  [x] false       --force
    string in                   [ ] -           --in <in>
    #[alias("i")]
    string in                   [ ] -           -i|--in <in>
    string in__input_file           [ ] -           --in <input-file>
    string in__input_file="/dev/null" [x] "/dev/null" --in [input-file] default: /dev/null

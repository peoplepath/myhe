# myhe
Multi YAML Headless Editor

## Usage
 ```bash
# show available commands
myhe list

# show help for particular command
myhe help delete

# show all values of all keys
myhe show /dir/with/yamls

# find YAML file where are particular keys (DB_*)
myhe find -p ^DB_ /dir/with/yamls

# delete all (nested) *.description keys
myhe delete -p .* -p ^description$ /dir/with/yamls

# or any description key and subkeys (recursion)
myhe delete -r -p ^description$ /dir/with/yamls

# look for non-standard extenstions (yaml|yml by default)
myhe show -e conf /dir/with/yamls

# simple validation for syntax errors
myhe validate /dir/with/yamls
```

## Todo
- [x] delete command (remove matching keys from found files)
- [ ] edit command (edit values by defined rules)
- [ ] unit tests of course
- [x] support for nested keys DB_*.value
- [ ] proper value matching

## License
MIT

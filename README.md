# Laravel SoftDeletes With Unix Timestamp

## Installing

```sh
$ composer require al-one/laravel-soft-deletes-unix -vvv
```


## Usage

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Alone\LaravelSoftDeletesUnix\Eloquent\SoftDeletesUnix;

class Flight extends Model
{

    use SoftDeletesUnix;

    protected $dateFormat = 'U';

    protected $dates = ['deleted_at'];
}
```

## License

MIT
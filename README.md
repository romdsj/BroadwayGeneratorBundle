[![Build Status](https://travis-ci.org/RomainDeSaJardim/BroadwayGeneratorBundle.svg?branch=master)](https://travis-ci.org/RomainDeSaJardim/BroadwayGeneratorBundle)
[![codecov](https://codecov.io/gh/RomainDeSaJardim/BroadwayGeneratorBundle/branch/master/graph/badge.svg)](https://codecov.io/gh/RomainDeSaJardim/BroadwayGeneratorBundle)

# BroadwayGeneratorBundle
This bundle gives some symfony commands to generate Commands, Events, ReadModels, and more, for Broadway.

This bundle is highly inspired by [SensioGeneratorBundle](https://github.com/sensiolabs/SensioGeneratorBundle)
 
 ## Installation
 
 Install it with composer
 
 ```composer require romaindesajardim/broadway-generator-bundle```
 
 and add it to your `AppKernel.php`
 
 ```new RomainDeSaJardim\Bundle\BroadwayGeneratorBundle\BroadwayGeneratorBundle(),```
 
 ## Features
 
 Actually there is only one command available. More will come soon ...
 
 ### Broadway Commands
 
 This command generate a Broadway command and implements the handle method in the Broadway Command Handler automatically. You have just to add your parameters and code the handler.
 
 ```php bin/console rdsj:broadway:generate-command```
 
 :warning: If you use Symfony 2, use `app/console` instead of `bin/console`
 
 This command need 3 inputs :
 
 * `bundle` _(The bundle name where the Broadway Command will be generate)_
 * `name` _(The name of the Broadway Command you want)_
 * `command-handler` Optionnal _(The Command Handler's service id where the Broadway Command will be handle)_
 
 By default the command is run in the interactive mode and asks questions to determine values of thoose inputs
 
 But if you want, you can run the command in a non-interactive mode and providing the needed inputs
 
 ```php bin/console rdsj:broadway:generate-command --no-interaction FooBarBundle FooBar [foo.bar-handler]```
 
 #### Exemple
 
 Imagine I have my Broadway Command Handler `FooBarBundle\FooBarCommandHandler` linked by a Symfony service :
 
 ```
<service id="foo.bar.command_handler" class="FooBarBundle\FooBarCommandHandler">
    <tag name="command_handler"/>
</service>
```

And I want to create a Broadway Command `FooBar` in the same bundle as my Command Handler `FooBarBundle`

So in a non interactive mode I have to launch this command :

`php bin/console rdsj:broadway:generate-command --no-interaction FooBarBundle FooBar foo.bar.command_handler`

The result will be :

A new file `Command\FooBarCommand.php`

```
<?php

namespace FooBarBundle\Command;


class FooBarCommand
{
    private $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUUID()
    {
        return $this->uuid;
    }
}
```

And a new handle method a the end of my Command Handler `FooBarCommandHandler.php`

```
public function handleFooBar(FooBarCommand $command)
{
    // @TODO Insert your code here
}
```

### Broadway Events
  
  This command generate a Broadway event and implements the handle method in the Broadway Projector automatically. You have just to add your parameters and code the handler.
  
  ```php bin/console rdsj:broadway:generate-event```
  
  :warning: If you use Symfony 2, use `app/console` instead of `bin/console`
  
  This command need 3 inputs :
  
  * `bundle` _(The bundle name where the Broadway Command will be generate)_
  * `name` _(The name of the Broadway Command you want)_
  * `projector` Optionnal _(The Projector's service id where the Broadway Event will be handle)_
  
  By default the command is run in the interactive mode and asks questions to determine values of thoose inputs
  
  But if you want, you can run the command in a non-interactive mode and providing the needed inputs
  
  ```php bin/console rdsj:broadway:generate-command --no-interaction FooBarBundle FooBar [foo.bar-projector]```
  
#### Exemple

  Same as Broadway Commands' one
  
  
### Broadway ReadModel's
  
  This command generate a Broadway ReadModel and add it to the service configuration of you're bundle. You have just to add your parameters and code the ReadModel.
  
  ```php bin/console rdsj:broadway:generate-readmodel```
  
  :warning: If you use Symfony 2, use `app/console` instead of `bin/console`
  
  This command need 3 inputs :
  
  * `bundle` _(The bundle name where the Broadway Command will be generate)_
  * `name` _(The name of the Broadway Command you want)_
  * `service-filename` Optionnal _(The service filename is the name of the file where your bundle services are configurated)
  
  By default the command is run in the interactive mode and asks questions to determine values of thoose inputs
  
  But if you want, you can run the command in a non-interactive mode and providing the needed inputs
  
  ```php bin/console rdsj:broadway:generate-readmodel --no-interaction FooBarBundle FooBar [services.xml]```
  
#### Exemple

  ```php bin/console rdsj:broadway:generate-readmodel --no interaction FooBarBundle FooBar services.xml```
  
  This command generate a ReadModel like this.
  
  ```
    <?php
    
    namespace FooBarBundle\ReadModel;
    
    use Broadway\ReadModel\Identifiable;
    
    class FooBarReadModel implements Identifiable
    {
        private $id;
    
        public function __construct($id)
        {
            $this->id = $id;
        }
    
        public function getId()
        {
            return $this->id;
        }
    }

  ```
  
  and transform `service.xml`
  
  ```
    <?xml version="1.0"?>
    <response xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
      <services>
        <defaults public="false"/>
      </services>
    </response>

  ```
  
  in
  
  ```
    <?xml version="1.0"?>
    <response xmlns="http://symfony.com/schema/dic/services" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
      <services>
        <defaults public="false"/>
        <service id="toto.readmodel" class="Broadway\ReadModel\ReadModel">
          <factory method="create" service="broadway.read_model.repository_factory"/>
          <argument>toto.readmodel</argument>
          <argument>AppBundle\ReadModel\TotoReadModel</argument>
        </service>
      </services>
    </response>

  ```
  in order to configure the new ReadModel service

## TODO

- [ ] Add a command to apply or handle an event in a projector, processor or saga
- [x] Add a command to generate Broadway Event
- [x] Add a command to generate Broadway ReadModel
- [ ] Add a command to generate Broadway Command Handler
- [ ] Generate Command handler automatically if it doesn't exist on Broadway Command creation
- [ ] Add fields to Broadway Command to add fields and implements getters

## Reporting an issue or a feature request

You feel free to report a new issue or a feature requests if it doesn't already exist or is in the Todo list (for feature request)
 
## Contribute

You feel free to open pull request
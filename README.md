# TicketBundle
NTI ticket bundle is a symfony bundle that provide to your symfony application with multiples 
ticket management features. 

### Installation

1. Install the bundle using composer:

    ```
    $ composer require ntidev/ticket-bundle
    ```

2. Add the bundle configuration to the AppKernel

    ```php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new NTI\TicketBundle\NTITicketBundle(),
            ...
        );
    }
    ```

3. Modify config.yml

    ```yaml
    nti_ticket:
        ticket_service: # name of your TicketProcessInterface implementation class 
        documents_directory: # path to your documents project directory
        entities:
            resource:
                class: # your UserInterface implementation class Ex. AppBundle\Entity\User
                unique_field: uniqueId
                email_field: email
    
            contact:
                class: # your UserInterface implementation class Ex. AppBundle\Entity\Contact
                unique_field: uniqueId
                email_field: email
    
        email_client:
            provider: exchange # at the moment the bundle support exchange only.
            server: my.server
            account: my.account
            password: my.password
    ```



4. Update the database schema

    ```php
    $ php app/console doctrine:schema:update
    ```

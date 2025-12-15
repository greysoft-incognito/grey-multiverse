<p align="center">
    <img src="https://banners.beyondco.de/GreyMultiverse.png?theme=light&packageManager=&packageName=greysoft-incognito%2Fgrey-multiverse&pattern=stripes&style=style_2&description=A+collection+of+different+API+endpoints+intended+to+serve+different+purposes+within+the+GreySoft+Workspace+with+a+shared+codebase+and+a+single+control+and+management+interface.&md=1&showWatermark=0&fontSize=225px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg&widths=250&heights=400" width="1280" title="Social Card Blade Font Awesome 6 Icon">
</p>

## About Grey Multiverse

Grey Multiverse is a collection of resource indended to serve various needs around qreysoft, built with the intention of creating a unity for accessing and managing data in and around the *Greysoft* ecosystem.

### Running Queues

Laravel includes an Artisan command that will start a queue worker and process new jobs as they are pushed onto the queue. You may run the worker using the `queue:work` Artisan command. Note that once the `queue:work` command has started, it will continue to run until it is manually stopped or you close your terminal [Laravel Docs](https://laravel.com/docs/9.x/queues#running-the-queue-worker)

The system dispatches tasks and jobs requiring heavy system resource consumtion to a queue thereby limiting the strain on the user waiting for these tasks to complete. You may run the following command to begin processing all queues:

```bash
php artisan queue:work
```

To keep the `queue:work` process running permanently in the background, you should use a process monitor such as [Supervisor](https://laravel.com/docs/9.x/queues#supervisor-configuration) to ensure that the queue worker does not stop running.


### Running The Scheduler

Where it is not possible to run queues, the task scheduller has also been implemented as an alternative that will automaticaally proccess queues and other schedulled tasks:

```bash
* * * * * cd /installation-path && php artisan schedule:run >> /dev/null 2>&1
```

This is best run as a cron job.

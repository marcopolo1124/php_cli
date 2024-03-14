#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();

$application->register("filter_country")
    ->addArgument("country_code")
    ->setCode(function(InputInterface $input, OutputInterface $output): int {
        $filename = "services.csv";
        $country_code = $input->getArgument("country_code");

        $handle = fopen($filename, "r");
        if ($handle == FALSE){
            $output->writeln(["{$filename} can not be found"]);
            return Command::FAILURE;
        };

        $categories = fgetcsv($handle, 1000, ",");
        $results = array();

        $num_rows = 0;
        if ($country_code){
            while (($buffer = fgetcsv($handle, 1000, ",")) !== false ){
                $assoc = array_combine($categories, $buffer);
                if (strtolower($country_code) === strtolower($assoc["Country"])){
                    array_push($results, $assoc);
                    $num_rows += 1;
                }
            }
        } else {
            while (($buffer = fgetcsv($handle, 1000, ",")) !== false ){
                $assoc = array_combine($categories, $buffer);
                array_push($results, $assoc);
                $num_rows += 1;
            }
        }

        $response = array(
            "results" => $results,
            "rows" => $num_rows
        );

        $res_str = json_encode($response);
        $output->writeln($res_str);

        fclose($handle);
        return Command::SUCCESS;
    });


$application->run();
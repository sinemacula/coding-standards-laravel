<?php

namespace App\Domain;

#[AsCommand(name: 'app:send')]
class SendEmails
{
}

#[Deprecated]
#[AsCommand(name: 'app:prune')]
class PruneStuff
{
}

#[AsCommand(name: 'app:import')]
class ImportCommand
{
}

#[AsCommand(name: 'app:report')]
final class ReportStuff
{
}

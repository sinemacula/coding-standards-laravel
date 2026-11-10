<?php

namespace App\Domain;

#[Marker]
#[Other]
class FarGroup
{
}

#[Other]
#[Marker]
class NearGroup
{
}

#[Marker]class Glued
{
}

#[Other(Marker::class)]
class ArgumentOnly
{
}

#[Other(1), Marker]
class AfterArgument
{
}

@extends('layouts.app')

@section('content')
<div class="section-header">  
        <h1 style="color: #000">Dashboard</h1>
</div>

<div class="section-body">
    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Total Machine</h4>
                    </div>
                    <div class="card-body" style="margin-top: 10px;">
                        {{ $machineCount }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Active</h4>
                    </div>
                    <div class="card-body" style="margin-top: 10px;">
                        {{ $activeMachineCount }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>Sparepart</h4>
                    </div>
                    <div class="card-body" style="margin-top: 10px;">
                        {{ $partCount }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">   
                        <h4>Total Schedule</h4>
                    </div>
                    <div class="card-body" style="margin-top: 10px;">
                        {{ $scheduleCount }}
                    </div>    
                </div>            
            </div>
        </div>
    </div>
@endsection
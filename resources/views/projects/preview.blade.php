@extends('layout')

@section('title')
    <h1>🏠 معاينة مشروع {{$project->name}}</h1>
@endsection
<style>

  
</style>
@section('content')

<button onclick="generatePDF()" class="btn btn-primary">
    <i class="fa fa-file-pdf"></i> طباعة التقرير
</button>
    <div class="container preview-container  " id="print-section">
        
        <div class="header">
            <div class="company-name">
                <h1 class="company-name">{{$project->company->name}}</h1>
            </div>
            <div class="project-name">
                <h1 class="project-name">{{$project->name}}</h1>
            </div>
        </div>

        <table class="table table-bordered preview-table">
            @foreach($project->units as $unit)
            <tr>
                <td class="unit-number">نموذج <span>{{$unit->unit_number}}</span></td>
                <td>{{$unit->type}}</td>
                <td>{{$unit->rooms}} غرف</td>
                <td>المساحة {{$unit->area}} م </td>
                <td class="unit-price">{{number_format($unit->price)}} ريال</td>
            </tr>
            @endforeach
        </table>


    <div class="building-wrapper" dir="rtl">
        <div class="building-grid">
            @foreach ($unitsByFloor as $floor => $units)
            <div class="floor-row">
                <div class="floor-label">
                    الدور
                    <span class="floor-title">  {{ $floor }}</span>
                </div>    
                <div class="units-container">
                    @foreach ($units as $unit)
                    <div class="unit-cell {{ $unit->status }}">
                        <span class="unit-name">{{ $unit->type . ' ' . $unit->unit_number }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

    <div class="legend">
        <span class="legend-item"><span class="legend-dot sold"></span>مباع</span>
        <span class="legend-item"><span class="legend-dot available"></span>متاح</span>
        <span class="legend-item"><span class="legend-dot reserved"></span>محجوز</span>
    </div>

</div>
<script>

function generatePDF() {
    const originalTitle = document.title;
    document.title = "تقرير مشروع {{ $project->name }}";
    window.print();
    document.title = originalTitle;
}   
</script>
@endsection
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function print_pdf(Request $request)
    {
        $pdf_heading=!empty($request->heading_arr)?$request->heading_arr:[];
        $column_arr=!empty($request->column_arr)?$request->column_arr:[];
        $content_arr=!empty($request->content_arr)?$request->content_arr:[];
        // dd($pdf_heading,$column_arr,$content_arr);
        if(empty($pdf_heading) || empty($column_arr) || empty($content_arr)){
            dd('Data provided is insufficient to show PDF!');
        }
        else{
        $column_width=100/(count($column_arr));
        // dd($column_width);
        $pdf = \PDF::loadView('pdf.print_pdf',[
                'pdf_heading'=>$pdf_heading,
                'column_arr'=>$column_arr,
                'content_arr'=>$content_arr,
                'column_width'=>$column_width,
            ]);
             $pdf->setPaper('A4', 'potrait'); 
             return $pdf->stream('PDF.pdf');
            }
        }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

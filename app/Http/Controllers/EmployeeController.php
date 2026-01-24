<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Funcionários listados com sucesso',
            'data' => $employees,
            'total' => $employees->count()
        ]);
    }

    public function store(EmployeeRequest $request)
    {
        $employee = Employee::create($request->validated());
        return response()->json($employee, 201);
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json($employee);
    }

    public function update(EmployeeRequest $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->update($request->validated());
        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully']);
    }
}

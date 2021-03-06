@extends('layouts.app')
@section('content')

    <style>
        .answer {
            width: 81%;
            background-color: transparent;
            text-align: center;
            direction: rtl;
            margin: auto;
            padding: 25px 0px;
            display: inline-block !important;
        }

    </style>

    <div class="container">
        {{-- action="/users/update/{{$user->id}}" method="POST" --}}
        <center>
            <h1 class="col-8" style="color: #212529; overflow: hidden; text-align: center;right: 11%;">Edit Question
            </h1>
        </center>
        <center>
            <div class=" container edit-questions row answer" style="margin-right: 23%;">
                <form id="question-form" method="POST" action="/questions/update/{{ $question->id }}">
                    @csrf
                    <div class="step-one row">
                        <div class="col-12">
                            <label for="">Title:
                                <input type="text" name="title" id="title" value="{{ $question->question }}"
                                    class="form-control" required>
                            </label>

                            <label for="">Status:
                                <select name="status" id="status" class="form-control" required>
                                    <option value="0">Not Active</option>
                                    <option value="1" selected> Active</option>
                                </select>
                            </label>

                            <label for="">Correct answer:
                                <select name="correct_answer" id="status" class="form-control" required>
                                    @foreach ($question->answers as $answer)

                                        <option {{ $question->correct_answer_id == $answer->id ? 'selected' : '' }}
                                            value="{{ $answer->id }}">{{ $answer->answer }}</option>

                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-lg">Edit</button>
                        </div>


                    </div>

                </form>
            </div>
        </center>

        @php
            $i = 1;
        @endphp
        <div class="row answer">
            <h3>Edit Answers</h3>
            @foreach ($question->answers as $answer)

                <form action="/answer/update-answer/{{ $answer->id }}" method="POST">
                    @csrf

                    <label for="">Answer-{{ $i++ }}:
                        <input type="text" name="answer" id="" value="{{ $answer->answer }}" class="form-control row"
                            required></label>
                    <button class="btn btn-primary btn-sm">Edit</button>

                </form>
            @endforeach

        </div>
    </div>
@endsection

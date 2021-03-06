unit Unit1;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, ExtCtrls, RWG, MD5, ComCtrls, OleCtrls, SHDocVw;

type
  TForm1 = class(TForm)
    Image1: TImage;
    GroupBox1: TGroupBox;
    Button1: TButton;
    Button2: TButton;
    Button3: TButton;
    Button4: TButton;
    OpenDialog1: TOpenDialog;
    SaveDialog1: TSaveDialog;
    Panel1: TPanel;
    ProgressBar1: TProgressBar;
    Label1: TLabel;
    procedure Button1Click(Sender: TObject);
    procedure Button3Click(Sender: TObject);
    procedure Button4Click(Sender: TObject);
    procedure Button2Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  Form1: TForm1;
  RWGColorsArr: TRWGColorRecArr;
  SymbolsArr: TRWGSymbolsArr;
  ModifiedSymbolsArr: TRWGModifiedSymbolsArr;
  DataLines: TRWGDataLines;

  ColorsTimeArr: TRWGColorRecArr;
  SymbolsTimeArr: TRWGSymbolsArr;
  ModifiedSymbolsTimeArr: TRWGModifiedSymbolsArr;
  Time: TRWGTime;

  AnsiStr: String;
  
implementation

procedure CheckLinesCoords;
var
  DataLines: TRWGDataLineCoords;
  i: Integer;
  BorderColor: TColor;
begin
  BorderColor := clRed;
  DataLines := GetDataLinesCoords(Form1.Image1.Canvas, RWGColorsArr);
  for i := 1 to Length(DataLines) do
  if (DataLines[i].ColorName <> '') then
    begin
      Form1.Image1.Canvas.Pen.Color := BorderColor;
      Form1.Image1.Canvas.MoveTo(DataLines[i].x1, DataLines[i].y1);
      Form1.Image1.Canvas.LineTo(DataLines[i].x2, DataLines[i].y1);
      Form1.Image1.Canvas.LineTo(DataLines[i].x2, DataLines[i].y2);
      Form1.Image1.Canvas.LineTo(DataLines[i].x1, DataLines[i].y2);
      Form1.Image1.Canvas.LineTo(DataLines[i].x1, DataLines[i].y1);
    end;
end;

procedure AppendSpaceSymbol(var ModifiedSymbolsArr: TRWGModifiedSymbolsArr; xLen, yLen: Byte);
var
  Append: Boolean;
  i: Integer;
begin
  Append := false;
  for i := 1 to Length(ModifiedSymbolsArr) do
  if (not Append) and (ModifiedSymbolsArr[i].Symbol = '') then
    begin
      Append := true;
      ModifiedSymbolsArr[i].Symbol := ' ';
      ModifiedSymbolsArr[i].Frame.xLen := xLen;
      ModifiedSymbolsArr[i].Frame.yLen := yLen;
      ModifiedSymbolsArr[i].Vectors.Len := 0;
    end;
end;

{$R *.dfm}

procedure TForm1.Button1Click(Sender: TObject);
begin
  if Form1.OpenDialog1.Execute then
    begin
      Form1.Image1.Picture.LoadFromFile(Form1.OpenDialog1.FileName);
    end;
end;

procedure TForm1.Button3Click(Sender: TObject);
var
  i, count: Integer;
  AnsiStr: String;
  MD5String: AnsiString;
  myFile : TextFile;
  CurrentTeam: Byte;
begin
  count := 0;
  for i := 1 to Length(DataLines) do
  if DataLines[i].UserName <> '' then
    count := count + 1;

  if (count > 0) then
    begin
      if Form1.SaveDialog1.Execute then
        begin
          AssignFile(myFile, Form1.SaveDialog1.FileName);
          Rewrite(myFile);

          AnsiStr := '<?xml version="1.0" encoding="WINDOWS-1251"?>' + #13#10;
          AnsiStr := AnsiStr + '<gamedata>' + #13#10 + #13#10;

          AnsiStr := AnsiStr + '<time>' + #13#10;
          AnsiStr := AnsiStr + '<minutes>' + inttostr(Time.Minutes) + '</minutes>' + #13#10;
          AnsiStr := AnsiStr + '<seconds>' + inttostr(Time.Seconds) + '</seconds>' + #13#10;
          AnsiStr := AnsiStr + '</time>' + #13#10 + #13#10;

          MD5String := inttostr(Time.Minutes) + '0' + inttostr(Time.Seconds) + '0';

          AnsiStr := AnsiStr + '<teams>' + #13#10;
          CurrentTeam := 1;
          AnsiStr := AnsiStr + '<team index="1">' + #13#10;
          for i := 1 to Length(DataLines) do
          if DataLines[i].UserName <> '' then
            begin
              if (CurrentTeam <> DataLines[i].Team) then
              begin
                CurrentTeam := DataLines[i].Team;
                AnsiStr := AnsiStr + '</team>' + #13#10;
                AnsiStr := AnsiStr + '<team index="' + inttostr(CurrentTeam) + '">' + #13#10;
              end;

              MD5String := MD5String + 't' + inttostr(CurrentTeam) + '-';
              MD5String := MD5String + DataLines[i].Infantry + '-';
              MD5String := MD5String + DataLines[i].Tanks + '-';
              MD5String := MD5String + DataLines[i].Trucks + '-';
              MD5String := MD5String + DataLines[i].AirCrafts + '-';
              MD5String := MD5String + DataLines[i].AntiAircraft + '-';
              MD5String := MD5String + DataLines[i].Artillery + '-';
              MD5String := MD5String + DataLines[i].TrainsShips + '-';
              MD5String := MD5String + DataLines[i].Unknown + '-';

              AnsiStr := AnsiStr + '<player>' + #13#10;
              AnsiStr := AnsiStr + '<username>' + DataLines[i].UserName + '</username>' + #13#10;
              AnsiStr := AnsiStr + '<infantry>' + DataLines[i].Infantry + '</infantry>' + #13#10;
              AnsiStr := AnsiStr + '<tanks>' + DataLines[i].Tanks + '</tanks>' + #13#10;
              AnsiStr := AnsiStr + '<trucks>' + DataLines[i].Trucks + '</trucks>' + #13#10;
              AnsiStr := AnsiStr + '<aircrafts>' + DataLines[i].AirCrafts + '</aircrafts>' + #13#10;
              AnsiStr := AnsiStr + '<antiaircraft>' + DataLines[i].AntiAircraft + '</antiaircraft>' + #13#10;
              AnsiStr := AnsiStr + '<artillery>' + DataLines[i].Artillery + '</artillery>' + #13#10;
              AnsiStr := AnsiStr + '<trainsships>' + DataLines[i].TrainsShips + '</trainsships>' + #13#10;
              AnsiStr := AnsiStr + '<unknown>' + DataLines[i].Unknown + '</unknown>' + #13#10;
              AnsiStr := AnsiStr + '</player>' + #13#10 + #13#10;
            end;
          AnsiStr := AnsiStr + '</team>' + #13#10;
          AnsiStr := AnsiStr + '</teams>' + #13#10 + #13#10;

          AnsiStr := AnsiStr + '<md5>' + lowercase(md5f(MD5String)) + '</md5>' + #13#10;

          AnsiStr := AnsiStr + '</gamedata>' + #13#10;

          WriteLn(myFile, AnsiStr);
          CloseFile(myFile);
        end;
    end
  else
    ShowMessage('��� ������!');
end;

procedure TForm1.Button4Click(Sender: TObject);
begin
  Form1.Close;
end;

procedure TForm1.Button2Click(Sender: TObject);
begin
  if (Form1.OpenDialog1.FileName <> '') then
    begin
      GetDataLines(Form1.Image1.Canvas, RWGColorsArr, ModifiedSymbolsArr, DataLines, Form1.ProgressBar1, Form1.Label1);
      GetTime(Form1.Image1.Canvas,Time, ColorsTimeArr, ModifiedSymbolsTimeArr);
    end
  else
    ShowMessage('������� �� ��������!');
end;

procedure TForm1.FormCreate(Sender: TObject);
begin
  // ������ � ������� �� �������:
  RWGColorsArr := RWGGetColors('Data\bmp.clr');
  SymbolsArr := RWGGetSymbols('Data\bmp.msk');
  SymbolsToModified(SymbolsArr, ModifiedSymbolsArr);
  AppendSpaceSymbol(ModifiedSymbolsArr, 6, 10);


  // ������ �� ��������:
  ColorsTimeArr  := RWGGetColors('Data\time.clr');
  SymbolsTimeArr := RWGGetSymbols('Data\time.msk');
  SymbolsToModified(SymbolsTimeArr, ModifiedSymbolsTimeArr);
  AppendSpaceSymbol(ModifiedSymbolsTimeArr, 8, 12);
end;

end.

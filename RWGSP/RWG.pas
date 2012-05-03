unit RWG;

interface

uses
  Graphics, ComCtrls, StdCtrls, SysUtils
  // :DEBUG:
  , Dialogs;

const
  COLORS_NUM        = 12;
  COLORS_IN_ONE     = 3;
  COLOR_NAME_LENGTH = 20;
  VECTORS           = 225;
  MAX_RWG_SYMBOLS   = 255;
  NeutralColor      = clBlack;

  LINES_NUM         = 12;
  SEARCH_X_BEGIN    = 0;
  SEARCH_Y_BEGIN    = 206;
  SEARCH_X_END      = 790;
  SEARCH_Y_END      = 440;

  TIME_SEARCH_X_BEG = 210;
  TIME_SEARCH_Y_BEG = 450;
  TIME_SEARCH_X_END = 350;
  TIME_SEARCH_Y_END = 470;


  SCAN_LENGTH       = 4;
  USERNAME_BEG      = SEARCH_X_BEGIN;
  USERNAME_END      = 100;
  INFANTRY_BEG      = 101;
  INFANTRY_END      = 180;
  TANKS_BEG         = 181;
  TANKS_END         = 270;
  TRUCKS_BEG        = 271;
  TRUCKS_END        = 360;
  AIRCRAFTS_BEG     = 361;
  AIRCRAFTS_END     = 450;
  ANTIAIRCRAFTS_BEG = 451;
  ANTIAIRCRAFTS_END = 540;
  ARTILLERY_BEG     = 541;
  ARTILLERY_END     = 620;
  TRAINSSHIPS_BEG   = 621;
  TRAINSSHIPS_END   = 710;
  UNKNOWN_BEG       = 711;
  UNKNOWN_END       = SEARCH_X_END;

  DISTANCE_BETWEEN_TEAMS = 15;

type
  TColorName = String[COLOR_NAME_LENGTH];

  // �����:
  TRWGColor = array[1..COLORS_IN_ONE] of TColor;
  TRWGColorRec = record
    Name: TColorName;
    Colors: TRWGColor;
  end;
  TRWGColorRecArr = array[1..COLORS_NUM] of TRWGColorRec;

  // �����:
  TRWGVector = record
    x, y: Integer;
  end;

  TRWGVectors = record
    Vectors: array[1..VECTORS] of TRWGVector;
    Len: Byte;
  end;

  TRWGSymbol = record
    Vectors: TRWGVectors;
    Symbol: String[1];
  end;

  TRWGSymbolsArr = array[1..MAX_RWG_SYMBOLS] of TRWGSymbol;

  TRWGFrame = record
    xLen, yLen: Integer;
  end;

  TRWGModifiedSymbol = record
    Vectors: TRWGVectors;
    Symbol: String[1];
    Frame: TRWGFrame;
  end;

  TRWGModifiedSymbolsArr = array[1..MAX_RWG_SYMBOLS] of TRWGModifiedSymbol;

  TUserName     = String[20];
  TInfantry     = String[10];
  TTanks        = String[10];
  TTrucks       = String[10];
  TAirCrafts    = String[10];
  TAntiAircraft = String[10];
  TArtillery    = String[10];
  TTrainsShips  = String[10];
  TUnknown      = String[10];

  TRWGDataLine = record
    UserName: TUserName;
    Infantry: TInfantry;
    Tanks: TTanks;
    Trucks: TTrucks;
    AirCrafts: TAirCrafts;
    AntiAircraft: TAntiAircraft;
    Artillery: TArtillery;
    TrainsShips: TTrainsShips;
    Unknown: TUnknown;
    Team: Byte;
  end;

  TRWGDataLines = array[1..LINES_NUM] of TRWGDataLine;

  TRWGDataLineCoord = record
    x1, y1, x2, y2: Integer;
    ColorName: TColorName;
  end;

  TRWGDataLineCoords = array[1..LINES_NUM] of TRWGDataLineCoord;

  TRWGTime = record
    Minutes: Integer;
    Seconds: Byte;
  end;

  // ��������� ������:
  procedure RWGClearSymbols(var Symbol: TRWGSymbolsArr);
  function  RWGGetSymbols(FileName: String): TRWGSymbolsArr;
  procedure RWGClearModifiedSymbols(var Symbols: TRWGModifiedSymbolsArr);
  procedure SymbolsToModified(var SymbolsArr: TRWGSymbolsArr; var ModifiedSymbols: TRWGModifiedSymbolsArr);

  procedure RWGClearColors(var RWGColorsArr: TRWGColorRecArr);
  function  RWGGetColors(FileName: String): TRWGColorRecArr;
  
  procedure RWGClearDataLinesCoords(var Lines: TRWGDataLineCoords);
  function  GetColorByName(ColorsArr: TRWGColorRecArr; ColorName: TColorName): TRWGColorRec;
  function  GetColorByColorPart(ColorRecArr: TRWGColorRecArr; Color:TColor): TRWGColorRec;

  function  CheckDataLineExist(Canvas: TCanvas; x,y: Integer; var RWGColor: TRWGColorRec; var LineCoords: TRWGDataLineCoord): Boolean;
  function  GetDataLinesCoords(Canvas: TCanvas; var ColorRecArr: TRWGColorRecArr): TRWGDataLineCoords;

  procedure GetSymbol(Canvas: TCanvas; x,y: Integer; var Color: TRWGColorRec; var ModifiedSymbols: TRWGModifiedSymbolsArr; var GetSym: String; var ModSymbol: TRWGModifiedSymbol);
  procedure ClearDataLines(var DataLines: TRWGDataLines);
  function  GetDataLine(Canvas: TCanvas; var DataLineCoords: TRWGDataLineCoord; var ColorsArr: TRWGColorRecArr; var ModifiedSymbols, ReducedMS: TRWGModifiedSymbolsArr): TRWGDataLine;
  procedure GetDataLines(Canvas: TCanvas;var ColorsArr: TRWGColorRecArr; var ModifiedSymbols: TRWGModifiedSymbolsArr; var FuncResult: TRWGDataLines; var ProgressBar: TProgressBar; var Label1: TLabel);
  procedure GetTime(Canvas: TCanvas; var Time: TRWGTime; var ColorsTimeArr: TRWGColorRecArr; var ModifSymbols: TRWGModifiedSymbolsArr);
implementation

// ������� ����������, �������� ������ �� ��������:
procedure RWGClearSymbols(var Symbol: TRWGSymbolsArr);
var
  i: Byte;
begin
  for i := 1 to MAX_RWG_SYMBOLS do
    begin
      Symbol[i].Symbol := '';
      Symbol[i].Vectors.Len := 0;
    end;
end;

// �������� ������ �� �������� �� ��������� �����:
function RWGGetSymbols(FileName: String): TRWGSymbolsArr;
var
  SymbolsArr: TRWGSymbolsArr;
  SymbolsFile: File of TRWGSymbol;
  i: Integer;
begin
  RWGClearSymbols(SymbolsArr);
  AssignFile(SymbolsFile, FileName);
  Reset(SymbolsFile);
  i := 0;
  while not eof(SymbolsFile) do
    begin
      i := i + 1;
      Read(SymbolsFile, SymbolsArr[i]);
    end;
  CloseFile(SymbolsFile);
  RWGGetSymbols := SymbolsArr;
end;

// ������� ����������, �������� ������ �� �����. ��������:
procedure RWGClearModifiedSymbols(var Symbols: TRWGModifiedSymbolsArr);
var
  i: Integer;
begin
  for i := 1 to Length(Symbols) do
    Symbols[i].Symbol := '';
end;

// ��������������� ������� �������� � ����������������:
procedure SymbolsToModified(var SymbolsArr: TRWGSymbolsArr; var ModifiedSymbols: TRWGModifiedSymbolsArr);
var
  i, j: Integer;
begin
  RWGClearModifiedSymbols(ModifiedSymbols);
  for i := 1 to Length(SymbolsArr) do
  if (SymbolsArr[i].Symbol <> '') then
    begin
      ModifiedSymbols[i].Vectors := SymbolsArr[i].Vectors;
      ModifiedSymbols[i].Symbol  := SymbolsArr[i].Symbol;
      ModifiedSymbols[i].Frame.xLen := 0;
      ModifiedSymbols[i].Frame.yLen := 0;
      for j := 1 to SymbolsArr[i].Vectors.Len do
        begin
          if SymbolsArr[i].Vectors.Vectors[j].x > ModifiedSymbols[i].Frame.xLen then
            ModifiedSymbols[i].Frame.xLen := SymbolsArr[i].Vectors.Vectors[j].x;
          if SymbolsArr[i].Vectors.Vectors[j].y > ModifiedSymbols[i].Frame.yLen then
            ModifiedSymbols[i].Frame.yLen := SymbolsArr[i].Vectors.Vectors[j].y;
        end;
    end;
end;

function GetModifSymbolBySymbol(Symbol: String; ModSymbols:TRWGModifiedSymbolsArr): TRWGModifiedSymbol;
var
  i, len: Integer;
  FuncResult: Boolean;
begin
  i:= 1;
  len := Length(ModSymbols);
  FuncResult := false;
  while i <= len do
  // ���� ���� ���������� - ���������� ��������� ������:
  if (ModSymbols[i].Symbol <> '') and (ModSymbols[i].Symbol = Symbol) then
    begin
      FuncResult := true;
      GetModifSymbolBySymbol := ModSymbols[i];
    end;
  // ���� �� ������� �� ������ ���������� �� ���������� ������ ������:
  if (not FuncResult) then
    GetModifSymbolBySymbol.Symbol := '';
end;

// ������� ����������, �������� ������ �� ������:
procedure RWGClearColors(var RWGColorsArr: TRWGColorRecArr);
var
  i: Integer;
begin
  for i := 1 to Length(RWGColorsArr) do
    RWGColorsArr[i].Name := '';
end;

// �������� ������ �� ������ �� ��������� �����:
function RWGGetColors(FileName: String): TRWGColorRecArr;
var
  RWGColorsArr: TRWGColorRecArr;
  ColorsFile: File of TRWGColorRec;
  i: Byte;
begin
  RWGClearColors(RWGColorsArr);
  AssignFile(ColorsFile, FileName);
  Reset(ColorsFile);
  i := 0;
  while not eof(ColorsFile) do
    begin
      i := i + 1;
      Read(ColorsFile, RWGColorsArr[i]);
    end;
  CloseFile(ColorsFile);
  RWGGetColors := RWGColorsArr;
end;

// �������� ���� �� ��� �����:
function GetColorByName(ColorsArr: TRWGColorRecArr; ColorName: TColorName): TRWGColorRec;
var
  i: Integer;
  FuncResult: Boolean;
  DefaultColor: TRWGColorRec;
begin
  FuncResult := false;
  for i := 1 to Length(ColorsArr) do
  if ColorsArr[i].Name = ColorName then
    begin
      GetColorByName := ColorsArr[i];
      FuncResult     := true;
    end;
  if not FuncResult then
    begin
      DefaultColor.Name := '';
      GetColorByName := DefaultColor;
    end;
end;

// �������� ���� �� ����� �� ��� ������������:
function GetColorByColorPart(ColorRecArr: TRWGColorRecArr; Color:TColor): TRWGColorRec;
var
  i, j: Integer;
  FuncResult: Boolean;
  ColorRecDefault: TRWGColorRec;
begin
  FuncResult := false;
  for i := 1 to Length(ColorRecArr) do
  for j := 1 to Length(ColorRecArr[i].Colors) do
  if ColorRecArr[i].Colors[j] = Color then
    begin
      FuncResult := true;
      GetColorByColorPart := ColorRecArr[i];
    end;
  if not FuncResult then
    begin
      ColorRecDefault.Name := '';
      GetColorByColorPart := ColorRecDefault;
    end;
end;

// --------------------------------------------------------------------------------------------
// ���������� ��������� ������������� ��������:

// ������� ����������, �������� ���������� � ��������������� ����� � �������:
procedure RWGClearDataLinesCoords(var Lines: TRWGDataLineCoords);
var
  i: Integer;
begin
  for i := 1 to Length(Lines) do
    Lines[i].ColorName := '';
end;

// ����������� ���������� ������ ������:
function CheckDataLineExist(Canvas: TCanvas; x,y: Integer; var RWGColor: TRWGColorRec; var LineCoords: TRWGDataLineCoord): Boolean;
const
  MAX_LINE_HEIGHT = 20;
  MIN_LINE_HEIGHT = 5;
  BREAK = 2;
  MAX_LINE_WIDTH = (SEARCH_X_END - SEARCH_X_BEGIN);
  MIN_LINE_WIDTH = (SEARCH_X_END - SEARCH_X_BEGIN) - 200;
var
  i, j, k, WrapCount, lineWidth, lineHeight: Integer;
  Traversing: Boolean;
  Color: TColor;
begin
  i := y;
  LineCoords.x1 := x;
  LineCoords.y1 := y;
  LineCoords.x2 := x;
  LineCoords.y2 := y;
  LineCoords.ColorName := RWGColor.Name;
  WrapCount := 0;

  while (WrapCount < BREAK) do
    begin
      Traversing := false;
      for j := SEARCH_X_BEGIN to SEARCH_X_END do
        begin
          Color := Canvas.Pixels[j, i];
          for k := 1 to Length(RWGColor.Colors) do
          if (RWGColor.Colors[k] = Color) then
            begin
              Traversing := true;
              if (j < LineCoords.x1) then LineCoords.x1 := j;
              if (j > LineCoords.x2) then LineCoords.x2 := j;
              LineCoords.y2 := i;
            end;
        end;
      if not Traversing then
        WrapCount := WrapCount + 1
      else
        WrapCount := 0;
      i := i + 1;
    end;

  //�������� ������ � ������ ���������� ������ ������:
  lineWidth  := LineCoords.x2 - LineCoords.x1;
  lineHeight := LineCoords.y2 - LineCoords.y1;
  if (MIN_LINE_WIDTH <= lineWidth) and
     (lineWidth <= MAX_LINE_WIDTH) and
     (MIN_LINE_HEIGHT <= lineHeight) and
     (lineHeight <= MAX_LINE_HEIGHT) then
    CheckDataLineExist := true
  else
    begin
      LineCoords.ColorName := '';
      CheckDataLineExist := false;
    end;
end;

// �������� ���������� ����� � �������:
function GetDataLinesCoords(Canvas: TCanvas; var ColorRecArr: TRWGColorRecArr): TRWGDataLineCoords;
var
  x, y: Integer;
  DataLines: TRWGDataLineCoords;
  Color: TColor;
  RWGColor: TRWGColorRec;

  CurrentLine: Byte;
  Scanned, LineExist: Boolean;
begin
  RWGClearDataLinesCoords(DataLines);
  CurrentLine := 1;
  y := SEARCH_Y_BEGIN;

  while (y <= SEARCH_Y_END) and (CurrentLine <= Length(DataLines)) do
    begin
      x :=  SEARCH_X_BEGIN;
      Scanned := false;
      LineExist := false;

      while (x <= SEARCH_X_END) and (not Scanned) do
        begin
          Color := Canvas.Pixels[x, y];
          RWGColor := GetColorByColorPart(ColorRecArr, Color);
          if (RWGColor.Name <> '') then
            begin
              Scanned := true;
              LineExist := CheckDataLineExist(Canvas, x, y, RWGColor, DataLines[CurrentLine]);
              if (LineExist) then
                begin
                  y := y + (DataLines[CurrentLine].y2 - DataLines[CurrentLine].y1);
                  CurrentLine := CurrentLine + 1;
                end;
            end;
          x := x + 1;
        end;
      if (not LineExist) then y := y + 1;
    end;
  GetDataLinesCoords := DataLines;
end;

// ������� ����������, �������� ������ �� ������:
procedure ClearDataLine(var DataLine: TRWGDataLine);
begin
  DataLine.UserName     := '';
  DataLine.Infantry     := '';
  DataLine.Tanks        := '';
  DataLine.Trucks       := '';
  DataLine.AirCrafts    := '';
  DataLine.AntiAircraft := '';
  DataLine.Artillery    := '';
  DataLine.TrainsShips  := '';
  DataLine.Unknown      := '';
  DataLine.Team         := 1;
end;

procedure ClearDataLines(var DataLines: TRWGDataLines);
var
  i: Integer;
begin
  for i := 1 to Length(DataLines) do
    ClearDataLine(DataLines[i]);
end;

function PixelCheck(Canvas:TCanvas; x, y: Integer; var Color: TRWGColorRec): Boolean;
var
  FuncResult: Boolean;
  CurrentColor: TColor;
  i: Integer;
begin
  FuncResult := false;
  CurrentColor := Canvas.Pixels[x, y];

  for i := 1 to Length(Color.Colors) do
  if (Color.Colors[i] = CurrentColor) then
    FuncResult := true;

  PixelCheck := FuncResult;
end;

// ��������� "�����������" ��������� �������:
function CheckSymbolFrame(Canvas:TCanvas; x,y:Integer; var Color:TRWGColorRec; var ModifSymbol:TRWGModifiedSymbol): Boolean;
const
  INDENT = 2;
var
  i, xLen, yLen: Integer;
  FuncResult: Boolean;
begin
  FuncResult := true;
  xLen := ModifSymbol.Frame.xLen + INDENT;
  yLen := ModifSymbol.Frame.yLen + INDENT;
  
  // ������� �������:
  i := x;
  while (x <= i) and (i <= (x + xLen)) and FuncResult do
    begin
      if PixelCheck(Canvas, i, y, Color) then
        FuncResult := false;

      i := i + 1;
    end;
  // ������ �������:
  i := x;
  while (x <= i) and (i <= (x + xLen)) and FuncResult do
    begin
      if PixelCheck(Canvas, i, y+yLen, Color) then
        FuncResult := false;

      i := i + 1;
    end;

  // ����� �������:
  i := y;
  while (y <= i) and (i <= (y + yLen)) and FuncResult do
    begin
      if PixelCheck(Canvas, x, i, Color) then
        FuncResult := false;

      i := i + 1;
    end;
    
  // ������ �������:
  i := y;
  while (y <= i) and (i <= (y + yLen)) and FuncResult do
    begin
      if PixelCheck(Canvas, x+xLen, i, Color) then
        FuncResult := false;

      i := i + 1;
    end;

  CheckSymbolFrame := FuncResult;
end;

function CheckSymbolPixels(Canvas:TCanvas; x,y:Integer; var Color:TRWGColorRec; var ModifSymbol:TRWGModifiedSymbol): Boolean;
var
  i: Integer;
  FuncResult: Boolean;
begin
  FuncResult := true;

  i := 1;
  while (i <= ModifSymbol.Vectors.Len) and FuncResult do
    begin
      if not PixelCheck(Canvas, x+ModifSymbol.Vectors.Vectors[i].x, y+ModifSymbol.Vectors.Vectors[i].y,  Color) then
        FuncResult := false;

      i := i + 1;
    end;

  CheckSymbolPixels := FuncResult;
end;

function CheckSymbolMaskPixels(Canvas:TCanvas; x,y:Integer; var Color:TRWGColorRec; var ModifSymbol:TRWGModifiedSymbol): Boolean;
var
  i, j, k, dX, dY: Integer;
  FuncResult, Scan: Boolean;
begin
  FuncResult := true;

  for i := x to (x + ModifSymbol.Frame.xLen) do
  for j := y to (y + ModifSymbol.Frame.yLen) do
  if FuncResult then
    begin
      if (PixelCheck(Canvas, i, j, Color)) then
        begin
          dX := i - x;
          dY := j - y;
          Scan := false;
          for k := 1 to ModifSymbol.Vectors.Len do
          if (dX = ModifSymbol.Vectors.Vectors[k].x) and
             (dY = ModifSymbol.Vectors.Vectors[k].y) then
            Scan := true;
          if (not Scan) then FuncResult := false;
        end;
    end;

  CheckSymbolMaskPixels := FuncResult;
end;

// ��������� ������� �� ������� ������� ������������ �����:
procedure GetSymbol(Canvas: TCanvas; x,y: Integer; var Color: TRWGColorRec; var ModifiedSymbols: TRWGModifiedSymbolsArr; var GetSym: String; var ModSymbol: TRWGModifiedSymbol);
var
  i, len: Integer;
  FuncResult: Boolean;
begin
  GetSym := '';
  FuncResult := false;
  len := Length(ModifiedSymbols);
  i := 1;

  while (i <= len) and (not FuncResult) do
    begin
      if (ModifiedSymbols[i].Symbol <> '') then
        begin
          // �������� �� ����������� � �����. �����:
          if CheckSymbolPixels(Canvas, x+1, y+1, Color, ModifiedSymbols[i])     and
             CheckSymbolFrame(Canvas, x, y, Color, ModifiedSymbols[i])          and
             CheckSymbolMaskPixels(Canvas, x+1, y+1, Color, ModifiedSymbols[i]) then
            begin
              GetSym  := ModifiedSymbols[i].Symbol;
              ModSymbol := ModifiedSymbols[i];
              FuncResult := true;
            end;
        end;
      i := i + 1;
    end;
end;

procedure EngToRusTransliteration(var DateLine: TRWGDataLine);
var
  i: Integer;
  HaveRusLetters: Boolean;
  SymbolCode: Integer;
begin
  HaveRusLetters := false;
  for i := 1 to Length(DateLine.UserName) do
  begin
    SymbolCode := ord(DateLine.UserName[i]);
    if (192 <= SymbolCode) and (SymbolCode <= 255) then
      HaveRusLetters := true;
  end;

  if (HaveRusLetters) then
  for i := 1 to Length(DateLine.UserName) do
  begin
    if (DateLine.UserName[i] = 'a') then DateLine.UserName[i] := '�';
    if (DateLine.UserName[i] = 'o') then DateLine.UserName[i] := '�';
    if (DateLine.UserName[i] = 'p') then DateLine.UserName[i] := '�';
    if (DateLine.UserName[i] = 'c') then DateLine.UserName[i] := '�';
    if (DateLine.UserName[i] = 'B') then DateLine.UserName[i] := '�';
    if (DateLine.UserName[i] = 'C') then DateLine.UserName[i] := '�';
    if (DateLine.UserName[i] = 'X') then DateLine.UserName[i] := '�';
  end;
end; 

function GetDataLineTest(Canvas: TCanvas; var DataLineCoords: TRWGDataLineCoord; var ColorsArr: TRWGColorRecArr; var ModifiedSymbols, ReducedMS: TRWGModifiedSymbolsArr): TRWGDataLine;
const
  BEG_INDENT_X = 1;
  BEG_INDENT_Y = BEG_INDENT_X;
var
  x, y, supX, supY, infX, infY: Integer;
  DataLine: TRWGDataLine;
  Symbol: String;
  Color: TRWGColorRec;
  ModifSymbol: TRWGModifiedSymbol;

  SymbolScanned: Boolean;
begin
  ClearDataLine(DataLine);
  infX  := DataLineCoords.x1 - BEG_INDENT_X;
  supX  := DataLineCoords.x2;
  infY  := DataLineCoords.y1 - BEG_INDENT_Y;
  supY  := DataLineCoords.y1 + SCAN_LENGTH;
  Color := GetColorByName(ColorsArr, DataLineCoords.ColorName);

  x := infX;
  while (x <= supX) do
    begin
      SymbolScanned:= false;
      y := infY;
      while (y <= supY) and (not SymbolScanned) do
        begin
          if (x <= USERNAME_END) then
            GetSymbol(Canvas, x, y, Color, ModifiedSymbols, Symbol, ModifSymbol)
          else
            GetSymbol(Canvas, x, y, Color, ReducedMS, Symbol, ModifSymbol);

          if (Symbol <> '') then
            begin
              SymbolScanned:= true;
              if (USERNAME_BEG <= x) and (x <= USERNAME_END) then
                DataLine.UserName := DataLine.UserName + Symbol
              else if (INFANTRY_BEG <= x) and (x <= INFANTRY_END) then
                DataLine.Infantry := DataLine.Infantry + Symbol
              else if (TANKS_BEG <= x) and (x <= TANKS_END) then
                DataLine.Tanks := DataLine.Tanks + Symbol
              else if (TRUCKS_BEG <= x) and (x <= TRUCKS_END) then
                DataLine.Trucks := DataLine.Trucks + Symbol
              else if (AIRCRAFTS_BEG <= x) and (x <= AIRCRAFTS_END) then
                DataLine.AirCrafts := DataLine.AirCrafts + Symbol
              else if (ANTIAIRCRAFTS_BEG <= x) and (x <= ANTIAIRCRAFTS_END) then
                DataLine.AntiAircraft := DataLine.AntiAircraft + Symbol
              else if (ARTILLERY_BEG <= x) and (x <= ARTILLERY_END) then
                DataLine.Artillery:= DataLine.Artillery + Symbol
              else if (TRAINSSHIPS_BEG <= x) and (x <= TRAINSSHIPS_END) then
                DataLine.TrainsShips:= DataLine.TrainsShips + Symbol
              else if (UNKNOWN_BEG <= x) and (x <= UNKNOWN_END) then
                DataLine.Unknown := DataLine.Unknown + Symbol;
              x:= x + ModifSymbol.Frame.xLen + 1;
            end;
          y := y + 1;
        end;
      if (not SymbolScanned) then x := x + 1;
    end;

  DataLine.UserName := Trim(DataLine.UserName);
  EngToRusTransliteration(DataLine);
  GetDataLineTest := DataLine;
end;

// ����������� ������ �� �������� ������:
function GetDataLine(Canvas: TCanvas; var DataLineCoords: TRWGDataLineCoord; var ColorsArr: TRWGColorRecArr; var ModifiedSymbols, ReducedMS: TRWGModifiedSymbolsArr): TRWGDataLine;
const
  BEG_INDENT_X = 1;
  BEG_INDENT_Y = BEG_INDENT_X;
var
  x, y, supX, supY, infX, infY: Integer;
  DataLine: TRWGDataLine;
  Symbol: String;
  Color: TRWGColorRec;
  ModifSymbol: TRWGModifiedSymbol;
begin
  ClearDataLine(DataLine);
  infX  := DataLineCoords.x1 - BEG_INDENT_X;
  supX  := DataLineCoords.x2;
  infY  := DataLineCoords.y1 - BEG_INDENT_Y;
  supY  := DataLineCoords.y1 + SCAN_LENGTH;
  Color := GetColorByName(ColorsArr, DataLineCoords.ColorName);
  
  for x := infX to supX do
  for y := infY to supY do
    begin
      if (x <= USERNAME_END) then
        GetSymbol(Canvas, x, y, Color, ModifiedSymbols, Symbol, ModifSymbol)
      else
        GetSymbol(Canvas, x, y, Color, ReducedMS, Symbol, ModifSymbol);

      if (Symbol <> '') then
        begin
          if (USERNAME_BEG <= x) and (x <= USERNAME_END) then
            DataLine.UserName := DataLine.UserName + Symbol
          else if (INFANTRY_BEG <= x) and (x <= INFANTRY_END) then
            DataLine.Infantry := DataLine.Infantry + Symbol
          else if (TANKS_BEG <= x) and (x <= TANKS_END) then
            DataLine.Tanks := DataLine.Tanks + Symbol
          else if (TRUCKS_BEG <= x) and (x <= TRUCKS_END) then
            DataLine.Trucks := DataLine.Trucks + Symbol
          else if (AIRCRAFTS_BEG <= x) and (x <= AIRCRAFTS_END) then
            DataLine.AirCrafts := DataLine.AirCrafts + Symbol
          else if (ANTIAIRCRAFTS_BEG <= x) and (x <= ANTIAIRCRAFTS_END) then
            DataLine.AntiAircraft := DataLine.AntiAircraft + Symbol
          else if (ARTILLERY_BEG <= x) and (x <= ARTILLERY_END) then
            DataLine.Artillery:= DataLine.Artillery + Symbol
          else if (TRAINSSHIPS_BEG <= x) and (x <= TRAINSSHIPS_END) then
            DataLine.TrainsShips:= DataLine.TrainsShips + Symbol
          else if (UNKNOWN_BEG <= x) and (x <= UNKNOWN_END) then
            DataLine.Unknown := DataLine.Unknown + Symbol;
        end;
    end;
    GetDataLine := DataLine;
end;

// �������� ������ �� ���� �����:
procedure GetDataLines(Canvas: TCanvas;var ColorsArr: TRWGColorRecArr; var ModifiedSymbols: TRWGModifiedSymbolsArr; var FuncResult: TRWGDataLines; var ProgressBar: TProgressBar; var Label1: TLabel);
var
  DataLineCoords: TRWGDataLineCoords;
  i, j, count: Integer;
  ReducedMSArr: TRWGModifiedSymbolsArr;
  CurrentTeam: Byte;
begin
  ClearDataLines(FuncResult);
  DataLineCoords:= GetDataLinesCoords(Canvas, ColorsArr);

  // ������� ����. ��������� ��� �������� ������ ���� � �����:
  RWGClearModifiedSymbols(ReducedMSArr);
  count := 0;
  for i := 1 to Length(ModifiedSymbols) do
  if  (ModifiedSymbols[i].Symbol = '0') or
      (ModifiedSymbols[i].Symbol = '1') or
      (ModifiedSymbols[i].Symbol = '2') or
      (ModifiedSymbols[i].Symbol = '3') or
      (ModifiedSymbols[i].Symbol = '4') or
      (ModifiedSymbols[i].Symbol = '5') or
      (ModifiedSymbols[i].Symbol = '6') or
      (ModifiedSymbols[i].Symbol = '7') or
      (ModifiedSymbols[i].Symbol = '8') or
      (ModifiedSymbols[i].Symbol = '9') or
      (ModifiedSymbols[i].Symbol = '0') or
      (ModifiedSymbols[i].Symbol = '/') then
      begin
        count:= count +1;
        ReducedMSArr[count] := ModifiedSymbols[i];
      end;

  // ����� ��� ������������:
  count := 0;
  for i := 1 to Length(DataLineCoords) do
  if (DataLineCoords[i].ColorName <> '') then
    count := count + 1;
  
  if (count > 0) then
    begin
      ProgressBar.Position := (100 mod count);
      ProgressBar.Step := (100 div count);

      j := 1;
      CurrentTeam := 1;
      for i := 1 to Length(DataLineCoords) do
        if (DataLineCoords[i].ColorName <> '') then
          begin
            Label1.Caption := '����������� ������ �' + inttostr(j) + '.';
            FuncResult[j] := GetDataLineTest(Canvas, DataLineCoords[i], ColorsArr, ModifiedSymbols, ReducedMSArr);
            if (i > 1) and ((DataLineCoords[i].y1 - DataLineCoords[i-1].y2) >= DISTANCE_BETWEEN_TEAMS) then
              CurrentTeam:= CurrentTeam + 1;
            FuncResult[j].Team := CurrentTeam;
            j := j + 1;
            ProgressBar.StepIt;
          end;
        Label1.Caption := '������ ��������. ����� �����: ' + inttostr(j - 1) + '.';
    end;
end;

// �������� ���������� ������ � ��������:
function GetTimeDataLineCoords(ColorName: String): TRWGDataLineCoord;
begin
  GetTimeDataLineCoords.ColorName := ColorName;
  GetTimeDataLineCoords.x1        := TIME_SEARCH_X_BEG;
  GetTimeDataLineCoords.y1        := TIME_SEARCH_Y_BEG;
  GetTimeDataLineCoords.x2        := TIME_SEARCH_X_END;
  GetTimeDataLineCoords.y2        := TIME_SEARCH_Y_END;
end;

procedure GetTime(Canvas: TCanvas; var Time: TRWGTime; var ColorsTimeArr: TRWGColorRecArr; var ModifSymbols: TRWGModifiedSymbolsArr);
var
  DataLineCoord: TRWGDataLineCoord;
  DataLine: TRWGDataLine;
  TimeStr, Delim, ResStr, minStr, secStr: String;
  i: Byte;
  Delimiter: Boolean;
begin
  DataLineCoord := GetTimeDataLineCoords(ColorsTimeArr[1].Name);
  DataLine      := GetDataLineTest(Canvas, DataLineCoord, ColorsTimeArr, ModifSymbols, ModifSymbols);
  TimeStr       := DataLine.Infantry + DataLine.Tanks + DataLine.Trucks + DataLine.AirCrafts;

  ResStr := '';
  // ������� �������:
  for i := 1 to Length(TimeStr) do
  if (TimeStr[i] <> ' ') then ResStr := ResStr + TimeStr[i];
  TimeStr := ResStr;
  // ���������� ����������� �����:
  minStr := ''; secStr := '';
  Delimiter := false;
  Delim := 'm'; // ��� ��� ��� �������)
  for i := 1 to Length(TimeStr) do
  begin
    if (TimeStr[i] = Delim) then
      Delimiter := true
    else
    begin
      if Delimiter then
        secStr := secStr + TimeStr[i]
      else
        minStr := minStr + TimeStr[i];
    end;
  end;

  if not Delimiter then
  begin
    secStr := minStr;
    minStr := '0';
  end;

  Time.Minutes := strtoint(minStr);
  Time.Seconds := strtoint(secStr);
end;

end.

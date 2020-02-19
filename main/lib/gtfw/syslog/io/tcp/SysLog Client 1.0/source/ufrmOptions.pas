unit ufrmOptions;

interface

uses
  SysLogClient,

  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, ExtCtrls;

type
  TfrmOptions = class(TForm)
    grpSysLog: TGroupBox;
    edtPort: TEdit;
    Label1: TLabel;
    FlowPanel1: TFlowPanel;
    Button1: TButton;
    btnApply: TButton;
    btnOK: TButton;
    procedure btnOKClick(Sender: TObject);
    procedure FormShow(Sender: TObject);
    procedure btnApplyClick(Sender: TObject);
    procedure Button1Click(Sender: TObject);
    procedure FormClose(Sender: TObject; var Action: TCloseAction);
  private
    FSysLogClient: TSyslogClient;
    procedure Apply;
  public
    property SysLogClient: TSyslogClient read FSysLogClient write FSysLogClient;
  end;

var
  frmOptions: TfrmOptions;

implementation

{$R *.dfm}

procedure TfrmOptions.Apply;
var
  lNewPort: integer;
begin
  lNewPort := StrToInt(edtPort.Text);

  FSysLogClient.Options.Port := lNewPort;

  FSysLogClient.Stop;
  FSysLogClient.Activate;
end;

procedure TfrmOptions.btnApplyClick(Sender: TObject);
begin
  Apply;
end;

procedure TfrmOptions.btnOKClick(Sender: TObject);
begin
  Apply;
  Close;
end;

procedure TfrmOptions.Button1Click(Sender: TObject);
begin
  Close;
end;

procedure TfrmOptions.FormClose(Sender: TObject; var Action: TCloseAction);
begin
  Action := caFree;
end;

procedure TfrmOptions.FormShow(Sender: TObject);
begin
  if FSysLogClient = nil then
    raise Exception.Create('SysLogClient is not assigned');


end;

end.

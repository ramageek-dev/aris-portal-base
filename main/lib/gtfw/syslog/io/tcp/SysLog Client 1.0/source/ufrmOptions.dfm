object frmOptions: TfrmOptions
  Left = 0
  Top = 0
  Caption = 'Options'
  ClientHeight = 126
  ClientWidth = 339
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'Tahoma'
  Font.Style = []
  OldCreateOrder = False
  Position = poScreenCenter
  OnClose = FormClose
  OnShow = FormShow
  PixelsPerInch = 96
  TextHeight = 13
  object grpSysLog: TGroupBox
    Left = 8
    Top = 8
    Width = 323
    Height = 65
    Caption = 'SysLog Options'
    TabOrder = 0
    object Label1: TLabel
      Left = 16
      Top = 24
      Width = 20
      Height = 13
      Caption = 'Port'
    end
    object edtPort: TEdit
      Left = 120
      Top = 21
      Width = 121
      Height = 21
      TabOrder = 0
      Text = '9000'
    end
  end
  object FlowPanel1: TFlowPanel
    Left = 0
    Top = 85
    Width = 339
    Height = 41
    Align = alBottom
    BevelOuter = bvNone
    FlowStyle = fsRightLeftTopBottom
    TabOrder = 1
    ExplicitLeft = 80
    ExplicitTop = 104
    ExplicitWidth = 185
    object Button1: TButton
      AlignWithMargins = True
      Left = 258
      Top = 6
      Width = 75
      Height = 25
      Margins.Top = 6
      Margins.Right = 6
      Caption = 'Close'
      TabOrder = 0
      OnClick = Button1Click
    end
    object btnOK: TButton
      AlignWithMargins = True
      Left = 174
      Top = 6
      Width = 75
      Height = 25
      Margins.Top = 6
      Margins.Right = 6
      Caption = 'OK'
      TabOrder = 2
      OnClick = btnOKClick
    end
    object btnApply: TButton
      AlignWithMargins = True
      Left = 90
      Top = 6
      Width = 75
      Height = 25
      Margins.Top = 6
      Margins.Right = 6
      Caption = 'Apply'
      TabOrder = 1
      OnClick = btnApplyClick
    end
  end
end

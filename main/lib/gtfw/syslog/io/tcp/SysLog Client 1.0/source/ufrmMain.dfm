object frmMain: TfrmMain
  Left = 0
  Top = 0
  Caption = 'SysLog Client'
  ClientHeight = 378
  ClientWidth = 650
  Color = clBtnFace
  Font.Charset = DEFAULT_CHARSET
  Font.Color = clWindowText
  Font.Height = -11
  Font.Name = 'Tahoma'
  Font.Style = []
  Menu = mainmnu
  OldCreateOrder = False
  OnCreate = FormCreate
  OnDestroy = FormDestroy
  PixelsPerInch = 96
  TextHeight = 13
  object edtMain: TRichEdit
    Left = 0
    Top = 0
    Width = 650
    Height = 321
    Align = alClient
    PlainText = True
    ScrollBars = ssBoth
    TabOrder = 0
    ExplicitTop = -2
    ExplicitHeight = 340
  end
  object pnlMain: TFlowPanel
    Left = 0
    Top = 321
    Width = 650
    Height = 38
    Align = alBottom
    BevelOuter = bvNone
    TabOrder = 1
    ExplicitTop = 346
    object btnClear: TButton
      AlignWithMargins = True
      Left = 6
      Top = 6
      Width = 75
      Height = 25
      Margins.Left = 6
      Margins.Top = 6
      Caption = 'Clear'
      TabOrder = 0
      OnClick = btnClearClick
    end
    object btnSave: TButton
      AlignWithMargins = True
      Left = 90
      Top = 6
      Width = 94
      Height = 25
      Margins.Left = 6
      Margins.Top = 6
      Caption = 'Save Log As..'
      TabOrder = 2
      OnClick = btnSaveClick
    end
    object btnHide: TButton
      AlignWithMargins = True
      Left = 193
      Top = 6
      Width = 75
      Height = 25
      Margins.Left = 6
      Margins.Top = 6
      Caption = 'Hide'
      TabOrder = 1
      OnClick = btnHideClick
    end
  end
  object StatusBar1: TStatusBar
    Left = 0
    Top = 359
    Width = 650
    Height = 19
    Panels = <
      item
        Width = 50
      end>
    ExplicitLeft = 8
    ExplicitTop = 369
  end
  object XPManifest1: TXPManifest
    Left = 40
    Top = 8
  end
  object dlgsaveLog: TSaveDialog
    DefaultExt = 'txt'
    Filter = 'Text Files (*.txt)|*.txt'
    Left = 72
    Top = 8
  end
  object TrayIcon1: TTrayIcon
    BalloonTitle = 'Syslog Client'
    BalloonTimeout = 2000
    BalloonFlags = bfInfo
    PopupMenu = pmnuMain
    Visible = True
    OnDblClick = TrayIcon1DblClick
    Left = 104
    Top = 8
  end
  object mainmnu: TMainMenu
    Left = 168
    Top = 8
    object System1: TMenuItem
      Caption = 'System'
      object HidetoTray1: TMenuItem
        Caption = 'Hide to Tray'
        OnClick = HidetoTray1Click
      end
      object N2: TMenuItem
        Caption = '-'
      end
      object Exit1: TMenuItem
        Caption = 'Exit'
        OnClick = Exit1Click
      end
    end
    object Log1: TMenuItem
      Caption = 'Log'
      object SaveLogAs1: TMenuItem
        Caption = 'Save Log As...'
        OnClick = SaveLogAs1Click
      end
      object N1: TMenuItem
        Caption = '-'
      end
      object Clear1: TMenuItem
        Caption = 'Clear'
        OnClick = Clear1Click
      end
    end
    object ools1: TMenuItem
      Caption = 'Options'
      object Options1: TMenuItem
        Caption = 'SysLog Options...'
        OnClick = Options1Click
      end
      object N4: TMenuItem
        Caption = '-'
      end
      object AlwaysOnTop1: TMenuItem
        Caption = 'Always On Top'
        OnClick = AlwaysOnTop1Click
      end
    end
  end
  object pmnuMain: TPopupMenu
    Left = 136
    Top = 8
    object Show1: TMenuItem
      Caption = 'Show'
      OnClick = Show1Click
    end
    object N3: TMenuItem
      Caption = '-'
    end
    object Close1: TMenuItem
      Caption = 'Close'
      OnClick = Close1Click
    end
  end
  object ApplicationEvents: TApplicationEvents
    OnMinimize = ApplicationEventsMinimize
    Left = 200
    Top = 8
  end
end

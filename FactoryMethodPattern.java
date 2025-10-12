interface Button{
    void paint();
}
class WinButton implements Button{
    @Override
    public void paint(){
        System.out.println("Render a button in Windows style.");
    }
}
class MacButton implements Button{
    @Override
    public void paint(){
        System.out.println("Render a button in Mac style.");
    }
}
interface CheckBox{
    void paint();
}
class WinCheckBox implements CheckBox{
    @Override
    public void paint(){
        System.out.println("Render a checkbox in Windows style.");
    }
}
class MacCheckBox implements CheckBox{
    @Override
    public void paint(){
        System.out.println("Render a checkbox in Mac style.");
    }
}
interface GUIFactory {
    Button createButton();
    CheckBox createCheckBox();
}
class WinFactory implements GUIFactory{
    @Override
    public Button createButton() {
        return new WindowsButton();
    }
    @Override
    public CheckBox createCheckBox() {
        return new WindowsCheckBox();
    }
}
class MacFactory implements GUIFactory{
    @Override
    public Button createButton() {
        return new MacButton();
    }
    @Override
    public CheckBox createCheckBox(){
        return new MacCheckBox();
    }
}
class Application {
    private GUIFactory factory;
    private Button button;
    Application(GUIFactory factory){
        this.factory = factory;
    }
    void createUI(){
        this.button=factory.createButton();
    }
    void paint(){
        button.paint();
    }
}
class ApplicationConfigurator{
    public static void main(String[] args) {
        config=readApplicationConfigFile();
        if (config.OS=="Windows"){
            factory=new WinFactory();
        } 
        else if (config.OS=="Mac") {
            factory=new MacFactory();
        }    
        else{
            throw new Exception("Error! Unknown operating system.");
        }
        Application app=new Application(factory);
    }
} 
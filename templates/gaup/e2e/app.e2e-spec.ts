import { GaupPage } from './app.po';

describe('gaup App', () => {
  let page: GaupPage;

  beforeEach(() => {
    page = new GaupPage();
  });

  it('should display welcome message', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('Welcome to app!');
  });
});
